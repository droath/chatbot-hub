<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Models;

use Droath\ChatbotHub\Casts\AsChatbotMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ChatbotMessages extends Model
{
    /** @var string[] */
    protected $fillable = [
        'owner_id',
        'owner_type',
        'message',
        'parent_id',
        'parent_type',
    ];

    /**
     * Get the owning model (User, Admin, Customer, etc.)
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
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
