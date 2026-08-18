<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['last_message_at' => 'datetime']; }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function worker(): BelongsTo { return $this->belongsTo(Worker::class); }
    public function application(): BelongsTo { return $this->belongsTo(Application::class); }
    public function messages(): HasMany { return $this->hasMany(Message::class); }
}
