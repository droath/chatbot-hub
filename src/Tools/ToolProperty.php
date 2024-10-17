<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Tools;

use Illuminate\Support\Str;

/**
 * Define the tool property creation class.
 */
final class ToolProperty
{
    /**
     * @var bool
     */
    public bool $required = false;
    /**
     * @var array
     */
    protected array $enum = [];
    /**
     * @var string|null
     */
    protected ?string $description = null;

    /**
     * @param string $name
     * @param string $type
     */
    private function __construct(
        public readonly string $name,
        protected readonly string $type,
    )
    {
    }

    /**
     * @param string $name
     * @param string $type
     *
     * @return self
     */
    public static function make(
        string $name,
        string $type,
    ): self
    {
        return new self(Str::snake($name), $type);
    }

    /**
     * @param array $enum
     *
     * @return $this
     */
    public function withEnums(array $enum): self
    {
        if ($this->type !== 'string') {
            throw new \RuntimeException(
                'Enum can only be used with string types.'
            );
        }

        $this->enum = $enum;

        return $this;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function describe(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return $this
     */
    public function required(): self
    {
        $this->required = true;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'enum' => $this->enum,
            'description' => $this->description,
        ];
    }
}
