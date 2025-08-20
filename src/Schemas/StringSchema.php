<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Schemas;

use Illuminate\Contracts\Support\Arrayable;

class StringSchema extends BaseSchema implements Arrayable
{
    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        return [
            'type' => 'string',
            'description' => $this->description,
        ];
    }
}
