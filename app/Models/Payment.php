<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Payment extends Model { use SoftDeletes; protected $guarded=[]; protected function casts():array{return ['paid_at'=>'datetime','subtotal'=>'decimal:2','platform_fee'=>'decimal:2','total'=>'decimal:2'];} public function application():BelongsTo{return $this->belongsTo(Application::class);} }
