<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany};
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use SoftDeletes;

    protected $table = 'marketplace_jobs';
    protected $guarded = [];
    public function getRouteKeyName(): string { return 'slug'; }
    protected function casts(): array { return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'application_deadline' => 'datetime', 'daily_rate' => 'decimal:2']; }
    public function getDurationDaysAttribute(): int { return max(1, (int) $this->starts_at?->startOfDay()->diffInDays($this->ends_at?->startOfDay()) + 1); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function category(): BelongsTo { return $this->belongsTo(JobCategory::class, 'job_category_id'); }
    public function requirements(): HasMany { return $this->hasMany(JobRequirement::class); }
    public function applications(): HasMany { return $this->hasMany(Application::class); }
    public function bookmarkedBy(): BelongsToMany { return $this->belongsToMany(Worker::class, 'bookmarks')->withTimestamps(); }
}
