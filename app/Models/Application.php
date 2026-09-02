<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function getRejectionQuoteAttribute(): string
    {
        $quotes = [
            'Kegagalan hari ini adalah awal dari kesuksesanmu yang lebih besar. Jangan pernah patah semangat, peluang emas terbaik sedang menantimu!',
            'Setiap pintu yang tertutup membuka jalan menuju pintu kesempatan yang jauh lebih berkah dan tepat untuk potensimu.',
            'Tetap percaya pada prosesmu. Kemampuan dan dedikasimu sangat bernilai. Terus asah skill dan raih pekerjaan impianmu berikutnya!',
            'Penolakan bukan berarti kamu kurang, melainkan ada peran yang lebih tepat dan luar biasa sedang disiapkan untukmu. Semangat selalu!',
        ];

        return $quotes[$this->id % count($quotes)];
    }
}
