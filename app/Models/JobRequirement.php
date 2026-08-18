<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class JobRequirement extends Model { protected $guarded=[]; protected function casts():array{return ['is_mandatory'=>'boolean'];} public function job():BelongsTo{return $this->belongsTo(Job::class);} }
