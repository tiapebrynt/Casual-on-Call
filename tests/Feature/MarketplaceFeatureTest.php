<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Company;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_jobs_can_be_filtered_by_daily_rate_range(): void
    {
        $companyUser = User::factory()->create();
        $companyUser->assignRole('company');
        $companyUser->company()->create([
            'name' => 'Nusa Hospitality',
            'slug' => 'nusa-hospitality',
            'city' => 'Jakarta',
            'verification_status' => 'verified',
        ]);

        $category = JobCategory::create([
            'name' => 'Hospitality',
            'slug' => 'hospitality',
        ]);

        $cheapJob = Job::create([
            'company_id' => $companyUser->company->id,
            'job_category_id' => $category->id,
            'title' => 'Barista Part Time',
            'slug' => 'barista-part-time',
            'description' => 'Membutuhkan barista untuk shift pagi dan malam.',
            'location' => 'Jakarta',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(3),
            'daily_rate' => 200000,
            'vacancies' => 2,
            'status' => 'published',
            'application_deadline' => now()->addHours(24),
        ]);

        $expensiveJob = Job::create([
            'company_id' => $companyUser->company->id,
            'job_category_id' => $category->id,
            'title' => 'Event Crew',
            'slug' => 'event-crew',
            'description' => 'Membutuhkan event crew untuk weekend.',
            'location' => 'Bandung',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(4),
            'daily_rate' => 600000,
            'vacancies' => 3,
            'status' => 'published',
            'application_deadline' => now()->addHours(24),
        ]);

        $response = $this->get(route('jobs.index', ['min_rate' => 150000, 'max_rate' => 350000]));

        $response->assertOk();
        $response->assertSee($cheapJob->title);
        $response->assertDontSee($expensiveJob->title);
    }

    public function test_completed_application_can_receive_rating_and_review(): void
    {
        $companyUser = User::factory()->create();
        $companyUser->assignRole('company');
        $companyUser->company()->create([
            'name' => 'Nusa Hospitality',
            'slug' => 'nusa-hospitality',
            'city' => 'Jakarta',
            'verification_status' => 'verified',
        ]);

        $workerUser = User::factory()->create();
        $workerUser->assignRole('worker');
        $workerUser->worker()->create([
            'city' => 'Jakarta',
            'verification_status' => 'verified',
        ]);

        $category = JobCategory::create([
            'name' => 'Hospitality',
            'slug' => 'hospitality',
        ]);

        $job = Job::create([
            'company_id' => $companyUser->company->id,
            'job_category_id' => $category->id,
            'title' => 'Barista Part Time',
            'slug' => 'barista-part-time-2',
            'description' => 'Membutuhkan barista untuk shift pagi dan malam.',
            'location' => 'Jakarta',
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->addDays(2),
            'daily_rate' => 250000,
            'vacancies' => 2,
            'status' => 'published',
            'application_deadline' => now()->subDay(),
        ]);

        $application = Application::create([
            'job_id' => $job->id,
            'worker_id' => $workerUser->worker->id,
            'cover_letter' => 'Saya siap bekerja.',
            'status' => 'completed',
            'responded_at' => now(),
        ]);

        $this->actingAs($workerUser)
            ->post(route('applications.review', $application), [
                'score' => 5,
                'title' => 'Profesional dan komunikatif',
                'body' => 'Kerja sama sangat baik dan tepat waktu.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('ratings', [
            'application_id' => $application->id,
            'reviewer_id' => $workerUser->id,
            'reviewee_id' => $companyUser->id,
            'score' => 5,
        ]);

        $this->assertDatabaseHas('reviews', [
            'body' => 'Kerja sama sangat baik dan tepat waktu.',
        ]);
    }

    public function test_jobs_can_be_filtered_by_rate_range_dropdown(): void
    {
        $companyUser = User::factory()->create();
        $companyUser->assignRole('company');
        $companyUser->company()->create([
            'name' => 'Nusa Hospitality',
            'slug' => 'nusa-hospitality-2',
            'city' => 'Jakarta',
            'verification_status' => 'verified',
        ]);

        $category = JobCategory::create([
            'name' => 'F&B',
            'slug' => 'f-and-b',
        ]);

        $cheapJob = Job::create([
            'company_id' => $companyUser->company->id,
            'job_category_id' => $category->id,
            'title' => 'Dishwasher Weekend',
            'slug' => 'dishwasher-weekend',
            'description' => 'Membantu kebersihan dapur.',
            'location' => 'Jakarta',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
            'daily_rate' => 150000,
            'payment_type' => 'daily',
            'vacancies' => 1,
            'status' => 'published',
            'application_deadline' => now()->addHours(12),
        ]);

        $response = $this->get(route('jobs.index', ['rate_range' => '100000-250000']));
        $response->assertOk();
        $response->assertSee('Dishwasher Weekend');
    }

    public function test_company_can_create_and_update_job_with_payment_type_and_expired_status(): void
    {
        $companyUser = User::factory()->create();
        $companyUser->assignRole('company');
        $company = $companyUser->company()->create([
            'name' => 'Event Pro ID',
            'slug' => 'event-pro-id',
            'city' => 'Jakarta',
            'verification_status' => 'verified',
        ]);

        $category = JobCategory::create([
            'name' => 'Events',
            'slug' => 'events',
        ]);

        $response = $this->actingAs($companyUser)->post(route('jobs.store'), [
            'job_category_id' => $category->id,
            'title' => 'SPG Booth Weekend',
            'description' => 'Menjaga booth pameran.',
            'location' => 'Jakarta',
            'daily_rate' => 350000,
            'payment_type' => 'project',
            'vacancies' => 2,
            'starts_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'ends_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'application_deadline' => now()->addHours(20)->format('Y-m-d\TH:i'),
            'status' => 'published',
            'requirements' => ['Minimal 18 tahun', 'Tinggi proporsional'],
        ]);

        $response->assertRedirect();
        $job = Job::where('title', 'SPG Booth Weekend')->first();
        $this->assertNotNull($job);
        $this->assertEquals('project', $job->payment_type);
        $this->assertEquals(2, $job->requirements()->count());

        $updateResponse = $this->actingAs($companyUser)->put(route('jobs.update', $job), [
            'job_category_id' => $category->id,
            'title' => 'SPG Booth Weekend Updated',
            'description' => 'Menjaga booth pameran terupdate.',
            'location' => 'Jakarta',
            'daily_rate' => 400000,
            'payment_type' => 'daily',
            'vacancies' => 3,
            'starts_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'ends_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'application_deadline' => now()->addHours(20)->format('Y-m-d\TH:i'),
            'status' => 'expired',
            'requirements' => ['Minimal 18 tahun'],
        ]);

        $updateResponse->assertRedirect();
        $job->refresh();
        $this->assertEquals('expired', $job->status);
        $this->assertEquals('daily', $job->payment_type);
    }

    public function test_worker_can_apply_job_with_custom_cv_file(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');

        $companyUser = User::factory()->create();
        $companyUser->assignRole('company');
        $company = $companyUser->company()->create([
            'name' => 'PT Resto Indonesia',
            'slug' => 'pt-resto-indonesia',
            'city' => 'Jakarta',
            'verification_status' => 'verified',
        ]);

        $category = JobCategory::create([
            'name' => 'Hospitality',
            'slug' => 'hospitality-cv',
        ]);

        $job = Job::create([
            'company_id' => $company->id,
            'job_category_id' => $category->id,
            'title' => 'Head Barista Shift',
            'slug' => 'head-barista-shift',
            'description' => 'Membutuhkan head barista berpengalaman untuk shift.',
            'location' => 'Jakarta',
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(5),
            'daily_rate' => 300000,
            'payment_type' => 'daily',
            'vacancies' => 2,
            'status' => 'published',
            'application_deadline' => now()->addDay(),
        ]);

        $workerUser = User::factory()->create();
        $workerUser->assignRole('worker');
        $worker = $workerUser->worker()->create([
            'city' => 'Jakarta',
            'verification_status' => 'verified',
        ]);

        $cvFile = \Illuminate\Http\UploadedFile::fake()->create('custom-cv-barista.pdf', 500, 'application/pdf');

        $response = $this->actingAs($workerUser)->post(route('applications.store', $job), [
            'cover_letter' => 'Saya melampirkan CV khusus pengalaman barista saya.',
            'cv' => $cvFile,
        ]);

        $response->assertRedirect();
        $application = Application::where('job_id', $job->id)->where('worker_id', $worker->id)->first();
        $this->assertNotNull($application);
        $this->assertNotNull($application->cv_path);
        \Illuminate\Support\Facades\Storage::disk('local')->assertExists($application->cv_path);
    }

    public function test_users_can_export_reports_as_csv(): void
    {
        $companyUser = User::factory()->create();
        $companyUser->assignRole('company');
        $companyUser->company()->create([
            'name' => 'PT Export Test',
            'slug' => 'pt-export-test',
            'city' => 'Jakarta',
            'verification_status' => 'verified',
        ]);

        // Export jobs
        $jobExport = $this->actingAs($companyUser)->get(route('reports.export.jobs'));
        $jobExport->assertOk();
        $this->assertStringContainsString('text/csv', $jobExport->headers->get('Content-Type'));

        // Export applications
        $appExport = $this->actingAs($companyUser)->get(route('reports.export.applications'));
        $appExport->assertOk();
        $this->assertStringContainsString('text/csv', $appExport->headers->get('Content-Type'));
    }

    public function test_dashboard_renders_for_company_and_worker(): void
    {
        $companyUser = User::factory()->create();
        $companyUser->assignRole('company');
        $companyUser->company()->create([
            'name' => 'PT Dashboard Test',
            'slug' => 'pt-dashboard-test',
            'city' => 'Jakarta',
            'verification_status' => 'verified',
        ]);
        $companyUser->wallet()->create(['balance' => 5000000]);

        $response = $this->actingAs($companyUser)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('COMPANY PORTAL');
        $response->assertSee('Export Laporan');

        $workerUser = User::factory()->create();
        $workerUser->assignRole('worker');
        $workerUser->worker()->create([
            'city' => 'Jakarta',
            'verification_status' => 'verified',
        ]);
        $workerUser->wallet()->create(['balance' => 1000000]);

        $responseWorker = $this->actingAs($workerUser)->get(route('dashboard'));
        $responseWorker->assertOk();
        $responseWorker->assertSee('WORKER PORTAL');
    }
}


