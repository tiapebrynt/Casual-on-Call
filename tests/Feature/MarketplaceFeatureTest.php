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
}
