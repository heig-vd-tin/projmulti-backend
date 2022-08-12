<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Requests\ProjectFormRequest;
use App\Http\Requests\PreferenceFormRequest;
use App\Models\Assignment;
use App\Models\Orientation;
use App\Models\Preference;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use App\Constants\UserRole;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    public function getAll(Request $request)
    {
        return Project::all();
    }

    public function getOwned(Request $request)
    {
        $user = $request->user();
        return Project::where('owner_id', $user->id)->get();
    }

    public function getPreferred(Request $request)
    {
        $user = $request->user();
        return $user->preferences->load('project');
    }

    public function getAssigned(Request $request)
    {
        $user = $request->user();
        return Project::find($user->assignments->pluck('id'));
    }

    public function createProject(ProjectFormRequest $request)
    {
        $user = $request->user();
        $project = Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'owner_id' => $user->id,
        ]);
        foreach ($request->orientations as $o) $orientations[$o['id']] = ['importance' => $o['importance']];
        $project->orientations()->attach($orientations);
        $project->tags()->attach($request->tags);
        return $project->fresh();
    }





    public function addPreference(Request $request)
    {
        $request->validate([
            'projects' => 'required|array|min:1|max:5',
            'projects.*.id' => 'required|integer',
            'projects.*.priority' => 'required|integer'
        ]);
        $user = $request->user();
        foreach ($request->projects as $project) {
            $preference = Preference::firstOrCreate([   //firstOrUpdate() ?
                'project_id' => $project['id'],
                'user_id' => $user->id,
            ]);
            $preference->priority = $project['priority'];
            $preference->save();
        }
        return $user->preferences->load('project');
    }

    public function removePreference(Request $request)
    {
        $request->validate([
            'projects' => 'required|array|min:1|max:5',
        ]);
        $user = $request->user();
        $user->preferences()->whereIn('project_id', $request->projects)->delete();
        return $user->preferences->load('project');
    }









    public function addAttribution(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'user_id' => 'required',
        ]);
        Assignment::updateOrCreate(
            ['user_id' => $request->user_id],
            ['project_id' => $request->project_id]
        );
    }

    public function removeAttribution(Request $request)
    {
        $request->validate(['user_id' => 'required']);
        Assignment::whereBelongsTo(User::find($request->user_id))->delete();
    }
}
