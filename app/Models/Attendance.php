<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Attendance extends Model { use SoftDeletes; protected $guarded=[]; protected function casts():array{return ['work_date'=>'date','clock_in_at'=>'datetime','clock_out_at'=>'datetime'];} public function application():BelongsTo{return $this->belongsTo(Application::class);} }
