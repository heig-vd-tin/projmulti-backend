<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Assignment;
use App\Models\Preference;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    public function getAll(Request $request)
    {
        $projects = Project::all();
        if ($request->user()->isAdmin())
            return $projects->load(['preferred_users', 'assigned_users']);
        else return $projects;
    }

    public function getOwned(Request $request)
    {
        $user = $request->user();
        return Project::where('owner_id', $user->id)->get()->load('assigned_users');
    }

    public function getPreferred(Request $request)
    {
        $user = $request->user();
        $ret = $user->preferences->load('project')->map(function ($item) {
            $a = $item->project;
            $a->priority = $item->priority;
            return $a;
        });
        return $ret;
    }

    public function getAssigned(Request $request)
    {
        $user = $request->user();
        return Project::find($user->assignments->pluck('id'));
    }

    public function createProject(Request $request)
    {
        $this->authorize('createProject', Project::class);
        $request->validate([
            'title' => 'required|max:100',
            'orientations' => 'required|array|min:1',
            'orientations.*.id' => 'required|integer',
            'orientations.*.importance' => 'required|integer',
            'tags' => 'array|max:3',
        ]);
        $user = $request->user();
        $project = Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'owner_id' => $user->id,
        ]);
        foreach ($request->orientations as $o) $orientations[$o['id']] = ['importance' => $o['importance']];
        $project->orientations()->attach($orientations);
        $project->tags()->attach($request->tags);
        return $project->fresh()->load('assigned_users');
    }

    public function editProject(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'title' => 'required|max:100',
            'orientations' => 'required|array|min:1',
            'orientations.*.id' => 'required|integer',
            'orientations.*.importance' => 'required|integer',
            'tags' => 'array|max:3',
        ]);
        $project = Project::findOrFail($request->id);
        $this->authorize('editProject', $project);
        $project->update([
            'title' => $request->title,
            'description' => $request->description
        ]);
        foreach ($request->orientations as $o) $orientations[$o['id']] = ['importance' => $o['importance']];
        $project->orientations()->sync($orientations);
        $project->tags()->sync($request->tags);
        return $project->fresh()->load('assigned_users');
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
            $preference = Preference::firstOrNew([
                'project_id' => $project['id'],
                'user_id' => $user->id,
            ]);
            $preference->priority = $project['priority'];
            $preference->save();
        }
        return $this->getPreferred($request);
    }

    public function removePreference(Request $request)
    {
        $request->validate([
            'projects' => 'required|array|min:1|max:5',
        ]);
        $user = $request->user();
        $user->preferences()->whereIn('project_id', $request->projects)->delete();
        return $this->getPreferred($request);
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
        User::findOrFail($request->user_id)->assignments()->delete();
    }
}
