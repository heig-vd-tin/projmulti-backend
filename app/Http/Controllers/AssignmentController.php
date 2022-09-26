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

    function fill_match_table(){
        $projects = Project::all();

        Match::truncate();

        foreach ($projects as $project) {
            foreach ($project->domains as $domain){
                $match_orientation = $this->get_orientation_from_domain($domain, false);
                $users = $project->preferred_users()
                        ->wherein('orientation_id', $match_orientation )
                        ->get();
                
                foreach ($users as $user){
                    $exist = $project->match_users()->where('user_id', '=', $user->id)->count();
                    if($exist > 0){
                        //dd($project, $user, $user->match_projects()->get());
                    }
                    else{
                        $p = Preference::where('project_id', '=', $project->id)
                        ->where('user_id', '=', $user->id)->first()->priority;

                        $user->match_projects()->attach($project, ['priority' => $p]);
                    }
                }
            }
        }
    }

    function assign_random($priority_max, $clear_table, $itteration){
        
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
    }

    function assign_level($level){
        $max_priority = Preference::max('priority');

        $users = User::all();

        if($level == 1){
            Assignment::truncate();
            //dd("Delete table");
        }

        $prjs = Project::Where('selected', '=', true)
            ->OrderBy('nb_student')
            ->get();

        foreach ($prjs as $project) {
            $needs = $project->domains;
            foreach ($needs as $i=>$n){
                $orientations = $this->get_orientation_from_domain($n, $level != 1 );
                
                $assigned_needs = DB::table('assignments')
                    ->join('users', 'user_id', '=', 'users.id')
                    ->where('project_id', '=', $project->id)
                    ->wherein('users.orientation_id', $orientations)
                    ->get();
                    //->pluck('orientation_id');

                if( $assigned_needs->count() > 0){
                    continue;
                    dd($n, $assigned_needs, $orientations, $project);
                }
               
                switch($level){
                    case 0:
                    case 1:
                        $priority = 2;
                        break;
                    case 2:
                        $priority = 3;
                        break;
                    case 3:
                        $priority = 4;
                        break;
                }
                $matches = DB::table('matches')
                    ->join('projects', 'matches.project_id', '=', 'projects.id')
                    ->join('users', 'matches.user_id', '=', 'users.id')
                    ->where('project_id', '=', $project->id)
                    ->where('priority', '<=', $priority)
                    ->wherein('users.orientation_id', $orientations)
                    ->orderBy('priority')
                    ->get();
                
                if( $matches->count() > 0 ){
                    //dd($matches);
                    foreach($matches as $m){
                        $std = User::find($m->user_id);
                        //dd($matches, $m, $std);
                        if( $std->assignments()->count() == 0 ){
                            $project->assigned_users()->attach($std, ['level' => $level]);
                            break;
                        }
                        else{
                            //dd($s);
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
        $nb_student_per_project = 5;
        $nb_prj_need = intdiv(User::where('role', 'like', 'student')->count(), $nb_student_per_project);

        $matches = DB::table('matches')
            ->select('project_id', DB::raw('count(*) as nb'))
            ->where('priority', '<=', 3)
            ->groupBy('project_id')
            ->orderBy('nb')
            ->get();

        foreach ($matches as $match) {
            $project_id = $match->project_id;
            $project = Project::find($project_id);

            $prof_have_project = Project::where('selected', '=', true)
                ->where('owner_id', $project->owner_id)
                ->count();

            if($prof_have_project == 0){
                $project->selected = true;
                $project->nb_student = $match->nb;
                $project->save();
                $nb_project++;
            }
        }

        $prof_without_project = User::where('role', 'like', 'professor')
            ->whereNotIn('id', function($query){
                $query->select('owner_id')->from('projects')->where('selected', '=', true);
            })->get();

        foreach ($prof_without_project as $prof) {
            $match_prj = DB::table('matches')
                ->select('project_id', 'owner_id', DB::raw('count(*) as nb'))
                ->join('projects', 'matches.project_id', '=', 'projects.id')
                ->where('owner_id', '=', $prof->id)
                ->groupBy('project_id')
                ->orderBy('nb')
                ->first();
            
            $project = Project::find($match_prj->project_id);
            if($project != null){
                $project->selected = true;
                $project->nb_student = $match_prj->nb;
                $project->save();
                $nb_project++;
            }
        }
        
        $match_free_prj = DB::table('matches')
                ->select('project_id', 'owner_id', DB::raw('count(*) as nb'))
                ->join('projects', 'matches.project_id', '=', 'projects.id')
                ->where('selected', '=', false)
                ->groupBy('project_id')
                ->orderBy('nb')
                ->get();

        foreach ($match_free_prj as $match) {
            if( $nb_prj_need <= $nb_project ){
                break;
            }

            $project = Project::find($match->project_id);
            
            $project->selected = true;
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

    public function assign(Request $request)
    {        
        //$this->fill_match_table();
        //$this->select_project();

        $this->assign_level(1);
        $this->assign_level(2);
        $this->assign_level(3);

        $this->calcul_score();
        $this->need_full();

        return $this->getAll($request);
    }



}
