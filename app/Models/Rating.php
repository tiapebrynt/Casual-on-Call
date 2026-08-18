<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\{BelongsTo,HasOne};
class Rating extends Model { protected $guarded=[]; public function application():BelongsTo{return $this->belongsTo(Application::class);} public function reviewer():BelongsTo{return $this->belongsTo(User::class,'reviewer_id');} public function reviewee():BelongsTo{return $this->belongsTo(User::class,'reviewee_id');} public function review():HasOne{return $this->hasOne(Review::class);} }
