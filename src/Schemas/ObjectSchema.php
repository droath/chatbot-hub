<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Schemas;

use Illuminate\Contracts\Support\Arrayable;

class ObjectSchema implements Arrayable
{
    /** @var array */
    protected array $properties = [];

    /**
     * @param string|null $name
     * @param bool $required
     * @param bool $additionalProperties
     */
    public function __construct(
        public ?string $name = null,
        public bool $required = false,
        protected bool $additionalProperties = false
    ) {}

    /**
     * @param array $properties
     *
     * @return $this
     */
    public function setProperties(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @param \Droath\ChatbotHub\Schemas\ObjectSchema|\Droath\ChatbotHub\Schemas\BaseSchema $property
     *
     * @return $this
     */
    public function addProperty(
        ObjectSchema|BaseSchema $property
    ): self
    {
        $this->properties[] = $property;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'type' => 'object',
            'required' => $this->required(),
            'properties' => collect($this->properties)->mapWithKeys(
                fn ($property) => [$property->name => $property->toArray()]
            )->toArray(),
            'additionalProperties' => $this->additionalProperties,
        ];
    }

    /**
     * @return array
     */
    protected function required(): array
    {
        return collect($this->properties)
            ->filter(function ($property) {
                return $property?->required ?? false;
            })
            ->pluck('name')
            ->toArray();
    }
}
