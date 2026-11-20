<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Assignment;
use App\Models\Preference;
use App\Models\ProjectMatch;
use App\Models\User;
use App\Services\AssignmentLocks;
use App\Services\AssignmentSolver;
use RuntimeException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AssignmentController extends Controller
{
    public function getAll(Request $request)
    {
        $user = $request->user();
        if ($user->isAdmin()){
            return DB::table('assignments')->get();
        }
    }

    public function getExportData(Request $request)
    {
        $user = $request->user();
        if ($user->isAdmin()){
            return DB::table('export_data')->get();
        }
    }

    public function loadData(Request $request)
    {
        $data = $request->validate([
            'data' => 'present|array',
            'data.*.project_id' => 'required|integer|exists:projects,id',
            'data.*.user_id' => 'required|integer|exists:users,id',
            'data.*.domain_id' => 'nullable|integer|exists:domains,id',
            'data.*.level' => 'nullable|integer',
        ]);
        // Les étudiants et projets verrouillés gardent leur équipe actuelle ; le reste est remplacé.
        return DB::transaction(function () use ($data) {
            $frozen = AssignmentLocks::frozenStudentIds();
            $lockedProjects = AssignmentLocks::lockedProjectIds();
            Assignment::query()->whereNotIn('user_id', $frozen)->delete();
            $loaded = 0;
            $skipped = 0;
            $seen = [];
            foreach ($data['data'] as $value) {
                $uid = $value['user_id'];
                if (in_array($uid, $frozen) || in_array($value['project_id'], $lockedProjects) || isset($seen[$uid])) {
                    $skipped++;
                    continue;
                }
                $seen[$uid] = true;
                Assignment::create([
                    'project_id' => $value['project_id'],
                    'user_id' => $uid,
                    'domain_id' => $value['domain_id'] ?? null,
                    'level' => $value['level'] ?? 0,
                ]);
                $loaded++;
            }
            return ['loaded' => $loaded, 'skipped' => $skipped, 'kept' => count($frozen)];
        });
    }

    public function get(Request $request, $id)
    {
        $assignment = Assignment::findOrFail($id);
        return $assignment->load(['user', 'project', 'domain']);
    }

    /** Orientations qui couvrent le domaine (table domain_orientation), élargies ou non. */
    function get_orientation_from_domain($domain, $enlarge)
    {
        return DB::table('domain_orientation')
            ->where('domain_id', $domain->id)
            ->when(!$enlarge, fn ($q) => $q->where('enlarged', false))
            ->pluck('orientation_id')
            ->all();
    }

    function fill_match_table($clean, $enlarge){
        $projects = Project::currentYear()->get();

        if($clean)
            ProjectMatch::query()->delete();

        foreach ($projects as $project) {
            foreach ($project->domains as $domain){
                $match_orientation = $this->get_orientation_from_domain($domain, $enlarge);
                $users = $project->preferred_users()
                        ->wherein('orientation_id', $match_orientation )
                        ->where('users.excluded', false)
                        ->get();
                
                foreach ($users as $user){
                    $exist = $project->matched_users()
                        ->where('user_id', '=', $user->id)
                        ->where('domain_id', '=', $domain->id)
                        ->count();

                    if($exist > 0){
                        //dd($project, $user, $user->match_projects()->get());
                    }
                    else{
                        $p = Preference::where('project_id', '=', $project->id)
                        ->where('user_id', '=', $user->id)->first()->priority;

                        $user->match_projects()->attach($project, ['priority' => $p, 'enlarge' => $enlarge, 'domain_id' => $domain->id]);
                    }
                }
            }
        }
    }


    function assign_auto($level){

        $clean = false;
        $enlarge = false;
        $max_preference = 3;
        $multi_orientation = false;

                switch($level){
                    case 0:
                        $clean = true;
                        break;
                    case 1:
                        $max_preference = 3;
                        break;
                    case 2:
                        $enlarge = true;
                        break;
                    case 3:
                        $max_preference = 4;
                        break;
                    case 4:
                        $enlarge = true;
                        $multi_orientation = true;
                        break;
                    case 5:
                        $max_preference = 4;
                        $enlarge = true;
                        break;
                    case 6:
                        $max_preference = 4;
                        $enlarge = true;
                        $multi_orientation = true;
                        break;
                }

                $max_nbr_user = 4;
                $max_priority = Preference::max('priority');
        
                if($clean){
                    Assignment::truncate();
                }
        
                $prjs = Project::currentYear()->where('selected', '=', true)
                    ->OrderBy('nb_student')
                    ->get();
       
                foreach ($prjs as $project) { 
                    $domains = $project->domains;
                    foreach ($domains as $d){

                    $matches = DB::table('matches')
                        ->join('projects', 'matches.project_id', '=', 'projects.id')
                        ->join('users', 'matches.user_id', '=', 'users.id')
                        ->where('project_id', '=', $project->id)
                        ->where('priority', '<=', $max_preference)
                        ->where('enlarge', '=', $enlarge)
                        ->where('domain_id', '=', $d->id)
                        ->orderBy('priority')
                        ->get();

                        $domain_assigned = DB::table('assignments')
                            ->where('project_id', '=', $project->id)
                            ->where('domain_id', '=', $d->id)
                            ->count();

                        $total_assigned = DB::table('assignments')
                            ->where('project_id', '=', $project->id)
                            ->count();

                        //if($project->id != 6)
                        //    dd("matches",$project->id, $d->id, $matches, $domain_assigned, $total_assigned);

                        if( $total_assigned < $max_nbr_user && $domain_assigned == 0){                            
                            //dd("max", $matches, $assigned);
                            foreach($matches as $m){
                                $std = User::find($m->user_id);
                                
                                $users_with_same_orientation = $project->assigned_users()->where('orientation_id', '=', $std->orientation_id)->get();

                                if( $std->assignments()->count() == 0 && (count($users_with_same_orientation) == 0 || $multi_orientation)){

                                    //if($project->id != 6)
                                    //    dd("matches",$project->id, $d->id, $matches, $domain_assigned, $total_assigned);

                                    $project->assigned_users()->attach($std, ['level' => $level, 'domain_id' => $d->id]);
                                    break;
                                }
                            }
                        }
                    }
                }

    }


    function need_full(){
        $projects = Project::currentYear()->get();
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
        $projects = Project::currentYear()->get();
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

        // Les brouillons n'entrent jamais dans la sélection.
        $drafts = Project::where('proposed', false)->pluck('id')->all();

        $matches = DB::table('matches')
            ->select('project_id', DB::raw('count(*) as nb'))
            ->whereNotIn('project_id', $drafts)
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
        $prof_without_project = User::whereIn('role', ['professor', 'admin'])
            ->whereIn('id', Project::currentYear()->select('owner_id'))
            ->whereNotIn('id', function($query){
                $query->select('owner_id')->from('projects')->where('selected', '=', true);
            })->get();

        foreach ($prof_without_project as $prof) {
            $match_prj = DB::table('matches')
                ->select('project_id', 'owner_id', DB::raw('count(*) as nb'))
                ->join('projects', 'matches.project_id', '=', 'projects.id')
                ->where('owner_id', '=', $prof->id)
                ->where('proposed', '=', true)
                ->where('enlarge', '=', false)
                ->groupBy('project_id')
                ->orderBy('nb', 'desc')
                ->first();
            if(!isset($match_prj))
                continue; // aucun étudiant n'a choisi les projets de ce prof

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
                ->where('proposed', '=', true)
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
        $projects = Project::currentYear()->get();

        foreach ($projects as $project) {
            $score = 0;
            $assigned_users = $project->assigned_users;
            
            foreach ($assigned_users as $user) {
                $score += $user->preferences()->where('project_id', $project->id)->first()?->priority ?? 0;
            }
            $project->score = $score;
            $project->save();
        }
    }


    public function calculMatch(Request $request){
        $this->fill_match_table(true, false);
        $this->fill_match_table(false, true);
    }

    public function copyAssignments($source){
        $max_level = Assignment::max('level');

        if($source < 10){
            $assignments = Assignment::where('level', '<=', 9)->get();
            $level = 10;
        }
        else{
            $assignments = Assignment::where('level', '=', $source)->get();
            $level = $source+1;
        }

        foreach ($assignments as $a) {
            $new = $a->replicate();
            $new->level = $level;
            $new->save();
        }
    }


    public function autoAffect(Request $request)
    {   
        /*     
        $this->assign_level(true, false, false, 2, false, 1);
        $this->assign_level(false, false, false, 3, false, 2);
        $this->assign_level(false, true, true, 3, false, 3);
        $this->assign_level(false, true, true, 3, true, 4);
        $this->assign_level(false, true, true, 4, false, 5);
        */

        $level = Assignment::max('level');
        if($level >= 10)
            return response()->json(['message' => 'La répartition a déjà été sauvegardée, impossible de la refaire.'], 422);

        // L'ancien algorithme efface toute la répartition : il ne respecte pas les verrous.
        if (AssignmentLocks::lockedProjectIds() || AssignmentLocks::frozenStudentIds())
            return response()->json(['message' => "Des projets ou des étudiants sont verrouillés et l'ancien algorithme efface toute la répartition. Utilisez « Calculer la répartition », qui respecte les verrous, ou déverrouillez-les d'abord."], 422);

        for($i=0; $i<=6; $i++)
            $this->assign_auto($i);

        //$this->copyAssignments(9);

        //$this->calcul_score();
        //$this->need_full();

        return $this->getAll($request);
    }

    public function selectProject(Request $request){        
        $this->select_project();
        return $this->getAll($request);
    }



    /** Répartition optimale (solveur CBC) ; remplace les affectations non verrouillées. */
    public function solve(Request $request)
    {
        $options = $request->validate([
            'min_size' => 'sometimes|integer|min:1|max:10',
            'max_size' => 'sometimes|integer|min:1|max:10|gte:min_size',
            'max_same_orientation' => 'sometimes|integer|min:1|max:10',
            'allow_fifth_choice' => 'sometimes|boolean',
            'allow_out_of_choice' => 'sometimes|boolean',
        ]);
        try {
            return (new AssignmentSolver($options))->solve();
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
