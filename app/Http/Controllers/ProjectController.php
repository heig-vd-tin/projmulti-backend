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

class ProjectController extends Controller
{
    public function getAll(Request $request)
    {
        //$user = $request->user();
        //$this->authorize('viewAll', Project::class);
        return Project::all();
    }

    public function getPreffered(Request $request)
    {
        $user = $request->user();
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
        $user = $request->user();
        $project = Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'owner_id' => $user->id,
        ]);
        foreach($request->orientations as $orientation){
            $project->orientations()->attach(Orientation::where('name', $orientation)->get(), ['importance' => $orientation['importance']]);
        }
        // $project->orientations()->attach(Orientation::whereIn('name', $request->orientations)->get());
        $project->tags()->attach(Tag::whereIn('name', $request->tags)->get());
        return $project->fresh();
    }

    public function addPreference(PreferenceFormRequest $request)
    {
        $user = $request->user();
        foreach ($request->projects_id as $i => $project_id) {
            $preference = Preference::firstOrCreate([
                'project_id' => $project_id,
                'user_id' => $user->id,
            ]);
            $preference->priority = $i + 1;
            $preference->save();
        }
        return $this->getPreffered($request);
    }

    public function removePreference(PreferenceFormRequest $request)
    {
        $user = $request->user();
        foreach ($request->projects_id as $project_id) {
            Preference::whereBelongsTo(Project::find($project_id))->whereBelongsTo($user)->delete();
        }
        return $this->getPreffered($request);
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
