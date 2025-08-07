<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Schemas;

abstract class BaseSchema
{
    /**
     * @param string $name
     * @param string $description
     * @param bool $required
     */
    public function __construct(
        public string $name,
        public string $description,
        public bool $required = false,
    ) {}
}
