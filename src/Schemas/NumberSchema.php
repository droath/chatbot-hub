<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Schemas;

use Illuminate\Contracts\Support\Arrayable;

class NumberSchema extends BaseSchema implements Arrayable
{
    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type' => 'number',
            'description' => $this->description,
        ];
    }
}
