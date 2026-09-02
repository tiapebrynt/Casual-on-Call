<?php

namespace App\Repositories;

use App\Models\Job;
use App\Repositories\Contracts\JobRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentJobRepository implements JobRepositoryInterface
{
    public function paginatePublished(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Job::query()
            ->with(['company.user', 'category'])
            ->withCount('applications')
            ->where('status', 'published');

        $search = $filters['search'] ?? null;
        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('company', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $category = $filters['category'] ?? null;
        if ($category !== null && $category !== '') {
            $query->where('job_category_id', $category);
        }

        $location = $filters['location'] ?? null;
        if ($location !== null && $location !== '') {
            $query->where('location', 'like', "%{$location}%");
        }

        $minRate = $filters['min_rate'] ?? null;
        $maxRate = $filters['max_rate'] ?? null;

        // Handle rate_range string (e.g. "100000-250000", "1000000-", "0-100000")
        $rateRange = $filters['rate_range'] ?? null;
        if ($rateRange && is_string($rateRange) && str_contains($rateRange, '-')) {
            [$rangeMin, $rangeMax] = explode('-', $rateRange, 2);
            if ($rangeMin !== '' && is_numeric($rangeMin)) {
                $minRate = $rangeMin;
            }
            if ($rangeMax !== '' && is_numeric($rangeMax)) {
                $maxRate = $rangeMax;
            }
        }

        if ($minRate !== null && $minRate !== '') {
            $query->where('daily_rate', '>=', (float) $minRate);
        }

        if ($maxRate !== null && $maxRate !== '') {
            $query->where('daily_rate', '<=', (float) $maxRate);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Job
    {
        return Job::create($data);
    }

    public function update(Job $job, array $data): Job
    {
        $job->update($data);
        return $job->refresh();
    }

    public function delete(Job $job): void
    {
        $job->delete();
    }
}
