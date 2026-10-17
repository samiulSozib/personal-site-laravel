<?php

namespace Tests\Feature\Admin;

use App\Models\Experience;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_create_update_and_delete_roles(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/experience')->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Experience/Index')->has('experiences', 0));

        $this->actingAs($admin)->get('/admin/experience/create')->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Experience/Form')->where('experience.current', true));

        $this->actingAs($admin)->post('/admin/experience', $this->payload())
            ->assertRedirect('/admin/experience')
            ->assertSessionHas('success', 'Role added.');

        $experience = Experience::query()->firstOrFail();
        $this->assertNull($experience->end_date);
        $this->assertSame(['Shipped search', 'Mentored two engineers'], $experience->highlights['en']);
        $this->assertSame(['Laravel', 'React'], $experience->stack);

        $this->actingAs($admin)->get("/admin/experience/{$experience->id}/edit")->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Experience/Form')->where('experience.company', 'Acme'));

        $this->actingAs($admin)->put("/admin/experience/{$experience->id}", $this->payload([
            'current' => false,
            'end_date' => '2025-12-31',
        ]))->assertRedirect('/admin/experience');

        $this->assertSame('2025-12-31', $experience->refresh()->end_date);

        $this->actingAs($admin)->delete("/admin/experience/{$experience->id}")->assertRedirect('/admin/experience');
        $this->assertDatabaseCount('experiences', 0);
    }

    public function test_end_date_must_not_be_before_start_date(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/experience', $this->payload(['current' => false, 'end_date' => '2019-01-01']))
            ->assertSessionHasErrors('end_date');
    }

    public function test_employment_type_must_be_a_known_value(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/experience', $this->payload(['employment_type' => 'volunteer']))
            ->assertSessionHasErrors('employment_type');
    }

    public function test_the_public_experience_page_lists_roles(): void
    {
        $this->actingAs($this->admin())->post('/admin/experience', $this->payload());

        $this->get('/en/experience')->assertOk()->assertInertia(fn ($page) => $page
            ->component('Experience')
            ->has('experiences', 1)
            ->where('experiences.0.company', 'Acme')
            ->where('experiences.0.current', true)
            ->where('experiences.0.highlights.bn', ['Shipped search', 'Mentored two engineers']));
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_replace([
            'company' => 'Acme',
            'company_url' => '',
            'role' => ['en' => 'Senior Engineer', 'bn' => ''],
            'employment_type' => 'full-time',
            'location' => ['en' => 'Dhaka', 'bn' => ''],
            'start_date' => '2022-03-01',
            'end_date' => '2024-01-01',
            'current' => true,
            'summary' => ['en' => 'Led the platform team.', 'bn' => ''],
            'highlights' => ['en' => ['Shipped search', ' ', 'Mentored two engineers'], 'bn' => []],
            'stack' => ['Laravel', 'React', ''],
        ], $overrides);
    }
}
