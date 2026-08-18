<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Relations\HasMany;
class JobCategory extends Model { use SoftDeletes; protected $guarded=[]; public function jobs():HasMany{return $this->hasMany(Job::class);} }
