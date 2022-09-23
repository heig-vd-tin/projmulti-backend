<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Assignment;
use App\Models\Preference;
use App\Models\User;
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

    function get_orientation_from_domain($domain)
    {
        switch ($domain->id){
            case '1': // meca
                return [7, 8];
            case '2': // electronic
                return [2];
            case '3': // electric
                return [2,3];
            case '4': // Thermic
                return [4,5,6];
            case '5': // Programming
                return [1];
        }
        return [];
    }

    function nbr_project_match($user){
        $projects = Project::inRandomOrder()->get();

        foreach ($projects as $project) {
            $match_orientation = $this->get_orientation_from_domain($project->domains);
            $pu = $project->preferred_users()
                    ->wherein('orientation_id', $match_orientation )
                    ->where('priority', '=', $priority)
                    ->get();
        }
    }

    function first_assign(){
        $max_priority = Preference::max('priority');

        $users = User::all();
        $projects = Project::inRandomOrder()->get();

        Assignment::truncate();

        foreach ($projects as $project) {
            $needs = $project->domains;
            foreach ($needs as $need) {

                $find = false;
                for ($priority = 1; !$find && $priority<=$max_priority; $priority++){
                    $match_orientation = $this->get_orientation_from_domain($need);
                    // list of student match need
                    $students = $project->preferred_users()
                        ->wherein('orientation_id', $match_orientation )
                        ->where('priority', '=', $priority)
                        ->get();
                        dd($students);
                    // assign only one project to one student
                    foreach($students as $s){
                        if( $s->assignments()->count() == 0 ){
                            $project->assigned_users()->attach($s);
                            $find = true;
                            break;
                        }
                        else{
                            //dd($s);
                        }
                    }
                    
                }
            }
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

    public function assign(Request $request)
    {
        //$this->authorize('assign', Assignment::class);
        $this->first_assign();
        $this->calcul_score();
        $this->need_full();

        return $this->getAll($request);
    }



}
