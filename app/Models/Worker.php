<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo,BelongsToMany,HasMany};
class Worker extends Model { use HasFactory, SoftDeletes; protected $guarded=[]; protected function casts():array{return ['birth_date'=>'date','is_available'=>'boolean'];} public function user():BelongsTo{return $this->belongsTo(User::class);} public function skills():BelongsToMany{return $this->belongsToMany(Skill::class,'worker_skills')->withPivot(['level','years']);} public function applications():HasMany{return $this->hasMany(Application::class);} public function bookmarks():BelongsToMany{return $this->belongsToMany(Job::class,'bookmarks')->withTimestamps();} }
