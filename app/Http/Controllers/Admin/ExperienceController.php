<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExperienceRequest;
use App\Models\Experience;
use App\Support\Content;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ExperienceController extends Controller
{
    public function index(): Response
    {
        $experiences = Experience::query()->ordered()->get()
            ->map(fn (Experience $experience) => [
                'id' => $experience->id,
                'company' => $experience->company,
                'role' => $experience->role['en'] ?? '',
                'employment_type' => $experience->employment_type,
                'location' => $experience->location['en'] ?? '',
                'start_date' => $experience->start_date,
                'end_date' => $experience->end_date,
                'stack' => $experience->stack ?? [],
            ]);

        return Inertia::render('Admin/Experience/Index', ['experiences' => $experiences]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Experience/Form', $this->formProps(new Experience([
            'role' => ['en' => '', 'bn' => ''],
            'employment_type' => 'full-time',
            'location' => ['en' => '', 'bn' => ''],
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'summary' => ['en' => '', 'bn' => ''],
            'highlights' => ['en' => [], 'bn' => []],
            'stack' => [],
        ])));
    }

    public function store(ExperienceRequest $request): RedirectResponse
    {
        Experience::query()->create($request->validated());

        return redirect()->route('admin.experience.index')->with('success', 'Role added.');
    }

    public function edit(Experience $experience): Response
    {
        return Inertia::render('Admin/Experience/Form', $this->formProps($experience));
    }

    public function update(ExperienceRequest $request, Experience $experience): RedirectResponse
    {
        $experience->update($request->validated());

        return redirect()->route('admin.experience.index')->with('success', 'Role saved.');
    }

    public function destroy(Experience $experience): RedirectResponse
    {
        $experience->delete();

        return redirect()->route('admin.experience.index')->with('success', 'Role deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formProps(Experience $experience): array
    {
        return [
            'experience' => [
                'id' => $experience->id,
                'company' => $experience->company ?? '',
                'company_url' => $experience->company_url ?? '',
                'role' => $experience->role ?? ['en' => '', 'bn' => ''],
                'employment_type' => $experience->employment_type,
                'location' => $experience->location ?? ['en' => '', 'bn' => ''],
                'start_date' => $experience->start_date,
                'end_date' => $experience->end_date ?? '',
                'current' => $experience->exists ? $experience->end_date === null : true,
                'summary' => $experience->summary ?? ['en' => '', 'bn' => ''],
                'highlights' => $experience->highlights ?? ['en' => [], 'bn' => []],
                'stack' => $experience->stack ?? [],
            ],
            'options' => [
                'employmentTypes' => Content::EMPLOYMENT_TYPES,
            ],
        ];
    }
}
