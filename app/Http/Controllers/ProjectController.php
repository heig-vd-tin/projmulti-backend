<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Requests\ProjectFormRequest;
use App\Http\Requests\PreferenceFormRequest;
use App\Models\Attribution;
use App\Models\Orientation;
use App\Models\Preference;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function getAll()
    {
        return Project::all();
    }

    public function getPreffered()
    {
        $user = User::find(1);
        $preferences = Preference::whereBelongsTo($user)->orderBy('priority')->get();
        $projects = collect();
        foreach ($preferences as $preference) {
            $projects->push(Project::find($preference->project_id));
        }
        return $projects;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProjectFormRequest $request)
    {
        $user = User::find(2);
        $project = Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'owner_id' => $user->id,
        ]);
        $project->orientations()->attach(Orientation::whereIn('name', $request->orientations)->get());
        $project->tags()->attach(Tag::whereIn('name', $request->tags)->get());
        return $project;
    }

    public function addPreference(PreferenceFormRequest $request)
    {
        $user = User::find(1);
        foreach ($request->projects_id as $i => $project_id) {
            $preference = Preference::firstOrCreate([
                'project_id' => $project_id,
                'user_id' => $user->id,
            ]);
            $preference->priority = $i + 1;
            $preference->save();
        }
        return $this->getPreffered();
    }

    public function removePreference(PreferenceFormRequest $request)
    {
        $user = User::find(1);
        foreach ($request->projects_id as $project_id) {
            $preference = Preference::whereBelongsTo(Project::find($project_id))->whereBelongsTo($user)->first();
            Preference::destroy($preference->id);
        }
        return $this->getPreffered();
    }

    public function addAttribution(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'user_id' => 'required',
        ]);
        Attribution::updateOrCreate(
            ['user_id' => $request->user_id],
            ['project_id' => $request->project_id]
        );
    }

    public function removeAttribution(Request $request)
    {
        $request->validate(['user_id' => 'required']);
        Attribution::whereBelongsTo(User::find($request->user_id))->delete();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        //
    }
}
