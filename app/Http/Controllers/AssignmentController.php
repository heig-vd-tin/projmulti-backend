<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Assignment;
use App\Models\Preference;
use App\Models\Match;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AssignmentController extends Controller
{
    public function getAll(Request $request)
    {
        $user = $request->user();
        if ($user->isAdmin())
            return Assignment::all()->load(['user', 'project']);
        else return $user->assignments->load('project');
    }

    public function get(Request $request, $id)
    {
        $assignment = Assignment::find($id);
        $this->authorize('getAssignment', $assignment);
        return $assignment->load(['user', 'project']);
    }

    function get_orientation_from_domain($domain, $enlarge)
    {
        switch ($domain->id){
            case '1': // meca
                return [7, 8];
            case '2': // electronic
                return $enlarge ? [2,7,3] : [2];
            case '3': // electric
                return $enlarge ? [2,3,7] : [2,3];
            case '4': // Thermic
                return [5,6];
            case '5': // Energy
                return [4];
            case '6': // Programming
                return $enlarge ? [1,2,7] : [1];
        }
        return [];
    }

    function fill_match_table($clean, $enlarge){
        $projects = Project::all();

        if($clean)
            Match::truncate();

        foreach ($projects as $project) {
            foreach ($project->domains as $domain){
                $match_orientation = $this->get_orientation_from_domain($domain, $enlarge);
                $users = $project->preferred_users()
                        ->wherein('orientation_id', $match_orientation )
                        ->get();
                
                foreach ($users as $user){
                    $exist = $project->matched_users()->where('user_id', '=', $user->id)->count();
                    if($exist > 0){
                        //dd($project, $user, $user->match_projects()->get());
                    }
                    else{
                        $p = Preference::where('project_id', '=', $project->id)
                        ->where('user_id', '=', $user->id)->first()->priority;

                        $user->match_projects()->attach($project, ['priority' => $p, 'enlarge' => $enlarge]);
                    }
                }
            }
        }
    }

    // tmz : clean and debug
    /*function assign_random($priority_max, $clear_table, $itteration){
        
        $nbr_assigned = 0;

        if($clear_table)
            Assignment::truncate();

        $matches = DB::table('matches')
                    ->join('projects', 'matches.project_id', '=', 'projects.id')
                    ->join('users', 'matches.user_id', '=', 'users.id')
                    ->where('priority', '<=', $priority_max)
                    ->inRandomOrder()
                    ->get();

        foreach ($matches as $match) {
            $project = Project::find($match->project_id);
            $usr = User::find($match->user_id);

            $needs = $project->domains;
            $orientations = [];
            foreach ($needs as $n){
                $orientations = array_merge($orientations, $this->get_orientation_from_domain($n, false));
            }
            //dd($needs, $orientations);

            $assigned_needs = DB::table('assignments')
                ->join('users', 'user_id', '=', 'users.id')
                ->where('project_id', '=', $match->project_id)
                ->where('level', '=', $itteration)
                ->wherein('users.orientation_id', $orientations)
                ->get();

            if( $assigned_needs->count() > 0){
                continue;
                dd($n, $assigned_needs, $orientations, $project);
            }

            $assigned_usr = DB::table('assignments')
                ->where('user_id', '=', $match->user_id)
                ->where('level', '=', $itteration)
                ->get();

            if( $assigned_usr->count() == 0 ){
                $project->assigned_users()->attach($usr, ['level' => $itteration]);
                $nbr_assigned++;
            }
        }
        return $nbr_assigned;
    }*/

    function assign_level($clean, 
            $enlarge_orientation, 
            $enlarge_matches,
            $max_preference,
            $level){

        $max_nbr_user = 4;
        $max_priority = Preference::max('priority');

        $users = User::all();

        if($clean){
            Assignment::truncate();
            //dd("Delete table");
        }

        $prjs = Project::Where('selected', '=', true)
            ->OrderBy('nb_student')
            ->get();

        foreach ($prjs as $project) {
            $needs = $project->domains;
            foreach ($needs as $i=>$n){
                $orientations = $this->get_orientation_from_domain($n, $enlarge_orientation );
                
                foreach ($orientations as $o){
                    $nbr_usr_need = 0;
                    foreach ($needs as $n){
                        $test = $this->get_orientation_from_domain($n, $enlarge_orientation );
                        if (in_array($o, $test)){
                            $nbr_usr_need++;
                        }
                    }

                    if($project->id == 6){
                        //dd($nbr_usr_need, $needs, $orientations, $o);
                    }

                    $total_assigned = DB::table('assignments')
                        ->where('project_id', '=', $project->id)
                        ->count();                        

                    $assigned_needs = DB::table('assignments')
                        ->join('users', 'user_id', '=', 'users.id')
                        ->where('project_id', '=', $project->id)
                        ->where('users.orientation_id', '=', $o)
                        ->get();

                    if( $assigned_needs->count() >= $nbr_usr_need || $total_assigned >= $max_nbr_user){
                        if($project->id == 8 && !in_array($o, [1,3])){
                            //dd("aaa",$assigned_needs, $o, $nbr_usr_need, $total_assigned, $max_nbr_user);
                        }
                        continue;
                        dd($n, $assigned_needs, $orientations, $project, $total_assigned);
                    }

                    $matches = DB::table('matches')
                        ->join('projects', 'matches.project_id', '=', 'projects.id')
                        ->join('users', 'matches.user_id', '=', 'users.id')
                        ->where('project_id', '=', $project->id)
                        ->where('priority', '<=', $max_preference)
                        ->where('enlarge', '=', $enlarge_matches)
                        ->where('users.orientation_id', '=', $o)
                        //->wherein('users.orientation_id', $orientations)
                        ->orderBy('priority')
                        ->get();
                    
                    if($project->id == 6 && !in_array($o, [1,2,7])){
                        //dd("stop 6", $matches, $orientations, $o);
                    }
                
                    if( $matches->count() > 0 ){
                        //dd($matches);
                        foreach($matches as $m){
                            $std = User::find($m->user_id);
                            //dd($matches, $m, $std);
                            
                            $users_with_same_orientation = $project->assigned_users()->where('orientation_id', '=', $std->orientation_id)->get();

                            if( $std->assignments()->count() == 0 && count($users_with_same_orientation) == 0){
                                $project->assigned_users()->attach($std, ['level' => $level]);
                                break;
                            }
                            else{
                                //dd($s);
                            }
                        }
                    }
                }
                //dd($project->id, $orientations, $project, $matches);//, $matches[0]->user()->get());
            }                
            //dd($project->id, $project, $matches);//, $matches[0]->user()->get());
        }
    }

    function need_full(){
        $projects = Project::all();
        foreach ($projects as $project) {
            $project->nb_student = $project->assigned_users()->count();
            $project->nb_domain = $project->domains()->count();

            if( $project->assigned_users()->count() == $project->domains()->count() ){
                $project->miss_student = false;
            }
            $project->save();
        }
    }

    function reset_project(){
        $projects = Project::all();
        foreach ($projects as $project) {
            $project->selected = false;
            $project->nb_student = 0;
            $project->nb_domain = 0;
            $project->score = 0;
            $project->miss_student = true;
            $project->save();
        }
    }

    function select_project(){
        $this->reset_project();

        $nb_project = 0;
        $nb_student_per_project = 4;
        $nb_prj_need = intdiv(User::where('role', 'like', 'student')->count(), $nb_student_per_project);

        $matches = DB::table('matches')
            ->select('project_id', DB::raw('count(*) as nb'))
            ->where('priority', '<=', 3)
            ->where('enlarge', '=', false)
            ->groupBy('project_id')
            ->orderBy('nb', 'desc')
            ->get();

        foreach ($matches as $match) {
            $project_id = $match->project_id;
            $project = Project::find($project_id);

            $prof_have_project = Project::where('selected', '=', true)
                ->where('owner_id', $project->owner_id)
                ->count();

            if($prof_have_project == 0){
                $project->selected = true;
                $project->score = 1;
                $project->nb_student = $match->nb;
                $project->save();
                $nb_project++;
            }
        }

        // pour les projets qui ne sont pas sélectionnés avant car il ne match pas
        $prof_without_project = User::where('role', 'like', 'professor')
            ->whereNotIn('id', function($query){
                $query->select('owner_id')->from('projects')->where('selected', '=', true);
            })->get();

        foreach ($prof_without_project as $prof) {
            $match_prj = DB::table('matches')
                ->select('project_id', 'owner_id', DB::raw('count(*) as nb'))
                ->join('projects', 'matches.project_id', '=', 'projects.id')
                ->where('owner_id', '=', $prof->id)
                ->where('enlarge', '=', false)
                ->groupBy('project_id')
                ->orderBy('nb', 'desc')
                ->first();
            if(!isset($match_prj))
                dd($prof, $match_prj);

            $project = Project::find($match_prj->project_id);
            if($project != null){
                $project->selected = true;
                $project->score = 2;
                $project->nb_student = $match_prj->nb;
                $project->save();
                $nb_project++;
            }
        }
        
        $match_free_prj = DB::table('matches')
                ->select('project_id', 'owner_id', DB::raw('count(*) as nb'))
                ->join('projects', 'matches.project_id', '=', 'projects.id')
                ->where('selected', '=', false)
                ->where('enlarge', '=', false)
                ->groupBy('project_id')
                ->orderBy('nb', 'desc')
                ->get();

        foreach ($match_free_prj as $match) {
            if( $nb_prj_need <= $nb_project ){
                break;
            }

            $project = Project::find($match->project_id);
            
            $project->selected = true;
            $project->score = 3;
            $project->nb_student = $match->nb;
            $project->save();
            $nb_project++;
        }
    }

    function calcul_score(){
        $projects = Project::all();

        foreach ($projects as $project) {
            $score = 0;
            $assigned_users = $project->assigned_users;
            
            foreach ($assigned_users as $user) {
                $score += $user->preferences()->where('project_id', $project->id)->first()->priority;
            }
            $project->score = $score;
            $project->save();
        }
    }

    function call_random($nbr, $off){
        for($itteration=0; $itteration<$nbr; $itteration++){
            $cpt_zero = 0;
            $tot = $this->assign_random(2, false, $itteration+$off);
            for($i=0; $i<100; $i++){
                $nb = $this->assign_random(2, false, $itteration+$off);
                $tot += $nb;
                if( $nb == 0 ){
                    $cpt_zero++;
                    if($cpt_zero > 10){
                        break;
                    }
                }
            }
            $cpt_zero = 0;
            for($itteration=0; $itteration<$nbr; $itteration++){
                $nb = $this->assign_random(3, false, $itteration+$off);
                $tot += $nb;
                if( $nb == 0 ){
                    $cpt_zero++;
                    if($cpt_zero > 10){
                        break;
                        //return $tot;
                    }
                }
            }
        }
    }

    public function rand(){
        $level = Assignment::max('level');
        //dd($level);
        if($level < 10)
            $level = 9;
        $nb = $this->call_random(2, $level+1);
    }

    public function calculMatch(Request $request){
        $this->fill_match_table(true, false);
        $this->fill_match_table(false, true);
    }

    public function autoAffect(Request $request)
    {        
        $this->assign_level(true, false, false, 2, 1);
        $this->assign_level(false, false, false, 3, 2);
        $this->assign_level(false, true, true, 3, 3);
        $this->assign_level(false, true, true, 4, 4);

        $this->calcul_score();
        $this->need_full();

        return $this->getAll($request);
    }

    public function selectProject(Request $request){        
        $this->select_project();
        return $this->getAll($request);
    }



}
