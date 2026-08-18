<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Skill extends Model { use SoftDeletes; protected $guarded=[]; public function workers():BelongsToMany{return $this->belongsToMany(Worker::class,'worker_skills')->withPivot(['level','years']);} }
