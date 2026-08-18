<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Review extends Model { use SoftDeletes; protected $guarded=[]; protected function casts():array{return ['is_visible'=>'boolean'];} public function rating():BelongsTo{return $this->belongsTo(Rating::class);} }
