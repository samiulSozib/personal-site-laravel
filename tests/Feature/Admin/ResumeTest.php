<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResumeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
        Setting::store('site', ['name' => ['en' => 'Test Person', 'bn' => 'টেস্ট']]);
    }

    public function test_cv_download_is_not_found_until_one_is_uploaded(): void
    {
        $this->get('/cv')->assertNotFound();
        $this->get('/en')->assertInertia(fn ($page) => $page->where('cv', null));
    }

    public function test_admin_uploads_a_cv_that_visitors_can_download(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/resume/cv', ['cv' => UploadedFile::fake()->create('my-resume.pdf', 120, 'application/pdf')])
            ->assertRedirect('/admin/resume')
            ->assertSessionHas('success', 'CV uploaded.');

        $cv = Setting::document('cv');
        Storage::disk('local')->assertExists($cv['path']);
        $this->assertSame('my-resume.pdf', $cv['name']);

        $this->get('/en')->assertInertia(fn ($page) => $page->where('cv.url', route('cv.download')));

        $this->get('/cv')
            ->assertOk()
            ->assertDownload('Test-Person-CV.pdf');

        $this->assertSame(1, Setting::document('cv')['downloads']);
    }

    public function test_replacing_the_cv_deletes_the_old_file_and_keeps_the_download_count(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/resume/cv', ['cv' => UploadedFile::fake()->create('one.pdf', 10, 'application/pdf')]);
        $first = Setting::document('cv')['path'];
        $this->get('/cv');

        $this->actingAs($admin)->post('/admin/resume/cv', ['cv' => UploadedFile::fake()->create('two.pdf', 10, 'application/pdf')]);

        Storage::disk('local')->assertMissing($first);
        $this->assertSame('two.pdf', Setting::document('cv')['name']);
        $this->assertSame(1, Setting::document('cv')['downloads']);
    }

    public function test_only_pdf_files_are_accepted(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/resume/cv', ['cv' => UploadedFile::fake()->create('cv.docx', 10, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')])
            ->assertSessionHasErrors('cv');

        $this->assertSame([], Setting::document('cv'));
    }

    public function test_admin_can_remove_the_cv(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/resume/cv', ['cv' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf')]);
        $path = Setting::document('cv')['path'];

        $this->actingAs($admin)->delete('/admin/resume/cv')->assertRedirect('/admin/resume');

        Storage::disk('local')->assertMissing($path);
        $this->get('/cv')->assertNotFound();
    }

    public function test_admin_uploads_and_removes_a_profile_photo(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/admin/resume/photo', ['photo' => UploadedFile::fake()->image('me.jpg', 400, 400)])
            ->assertRedirect('/admin/resume');

        $path = Setting::document('photo')['path'];
        Storage::disk('public')->assertExists($path);
        $this->get('/en/about')->assertInertia(fn ($page) => $page->where('photoUrl', Storage::disk('public')->url($path)));

        $this->actingAs($admin)->delete('/admin/resume/photo')->assertRedirect('/admin/resume');
        Storage::disk('public')->assertMissing($path);
        $this->get('/en/about')->assertInertia(fn ($page) => $page->where('photoUrl', null));
    }

    public function test_tiny_photos_are_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/resume/photo', ['photo' => UploadedFile::fake()->image('me.jpg', 50, 50)])
            ->assertSessionHasErrors('photo');
    }

    public function test_the_resume_page_needs_an_admin(): void
    {
        $this->get('/admin/resume')->assertRedirect('/login');

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->post('/admin/resume/cv', ['cv' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf')])
            ->assertForbidden();
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }
}
