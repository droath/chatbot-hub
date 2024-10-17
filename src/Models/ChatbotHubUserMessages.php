<?php

namespace Droath\ChatbotHub\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Define the chatbot messages model class.
 */
class ChatbotHubUserMessages extends Model
{
    protected $fillable = [
        'user_id',
        'messages',
    ];

    public function casts(): array
    {
        return [
            'messages' => 'json',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAuthUser(Builder $query): void
    {
        $query->where('user_id', auth()->id());
    }
}
