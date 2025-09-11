<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Scope;

class AgentMemory extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'agent_memory';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'key',
        'value',
        'expires_at',
    ];

    /**
     * Check if this memory entry is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Scope to get only non-expired entries.
     */
    #[Scope]
    protected function notExpired($query): void
    {
        $query->where('expires_at', '>', Carbon::now());
    }

    /**
     * Scope to get only expired entries.
     */
    #[Scope]
    protected function expired($query): void
    {
        $query->where('expires_at', '<=', Carbon::now());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'json',
            'expires_at' => 'datetime',
        ];
    }
}
