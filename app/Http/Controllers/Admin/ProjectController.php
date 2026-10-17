<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProjectRequest;
use App\Models\Project;
use App\Support\Content;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        $projects = Project::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Project $project) => [
                'id' => $project->id,
                'slug' => $project->slug,
                'name' => $project->name['en'] ?? '',
                'status' => $project->status,
                'sort_order' => $project->sort_order,
                'stack' => $project->stack,
                'featured' => $project->featured,
                'cover' => $project->cover,
            ]);

        return Inertia::render('Admin/Projects/Index', ['projects' => $projects]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Projects/Form', $this->formProps(new Project([
            'name' => ['en' => '', 'bn' => ''],
            'summary' => ['en' => '', 'bn' => ''],
            'role' => ['en' => '', 'bn' => ''],
            'period' => ['en' => '', 'bn' => ''],
            'highlights' => ['en' => [], 'bn' => []],
            'content' => ['en' => [], 'bn' => []],
            'stack' => [],
            'status' => 'in progress',
            'featured' => false,
            'sort_order' => 0,
        ])));
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        Project::query()->create($request->validated());

        return redirect()->route('admin.projects.index')->with('success', 'Project created.');
    }

    public function edit(Project $project): Response
    {
        return Inertia::render('Admin/Projects/Form', $this->formProps($project));
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return redirect()->route('admin.projects.index')->with('success', 'Project saved.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Project deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formProps(Project $project): array
    {
        return [
            'project' => [
                'id' => $project->id,
                'slug' => $project->slug,
                'name' => $project->name ?? ['en' => '', 'bn' => ''],
                'summary' => $project->summary ?? ['en' => '', 'bn' => ''],
                'role' => $project->role ?? ['en' => '', 'bn' => ''],
                'period' => $project->period ?? ['en' => '', 'bn' => ''],
                'highlights' => $project->highlights ?? ['en' => [], 'bn' => []],
                'content' => $project->content ?? ['en' => [], 'bn' => []],
                'stack' => $project->stack ?? [],
                'status' => $project->status,
                'cover' => $project->cover,
                'url' => $project->url,
                'repo' => $project->repo,
                'featured' => (bool) $project->featured,
                'sort_order' => $project->sort_order,
            ],
            'options' => [
                'statuses' => Content::PROJECT_STATUSES,
                'covers' => Content::COVERS,
                'blockTypes' => Content::BLOCK_TYPES,
            ],
        ];
    }
}
