<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Report extends Model { use SoftDeletes; protected $guarded=[]; protected function casts():array{return ['resolved_at'=>'datetime'];} public function reporter():BelongsTo{return $this->belongsTo(User::class,'reporter_id');} public function reportedUser():BelongsTo{return $this->belongsTo(User::class,'reported_user_id');} }
