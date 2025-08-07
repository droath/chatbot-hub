<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Schemas;

use Illuminate\Contracts\Support\Arrayable;

class JsonSchema implements Arrayable
{
    /** @var \Droath\ChatbotHub\Schemas\ObjectSchema */
    protected ObjectSchema $schema;

    /**
     * @param string $name
     */
    public function __construct(
        protected string $name
    ) {}

    /**
     * @param \Droath\ChatbotHub\Schemas\ObjectSchema $schema
     *
     * @return $this
     */
    public function setSchema(ObjectSchema $schema): self
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'type' => 'json_schema',
            'name' => $this->name,
            'schema' => $this->schema->toArray(),
        ];
    }
}
