<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Models;

use Droath\ChatbotHub\Casts\AsChatbotMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotMessages extends Model
{
    /** @var string[] */
    protected $fillable = [
        'user_id',
        'message',
        'parent_id',
        'parent_type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'message' => AsChatbotMessage::class,
        ];
    }
}
