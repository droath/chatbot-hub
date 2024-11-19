<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Tools;

use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Define the tool creation class.
 */
class Tool implements Arrayable
{
    /**
     * @var bool
     */
    protected bool $strict = false;

    /**
     * @var \Closure|null
     */
    protected ?\Closure $function = null;

    /**
     * @var string|null
     */
    protected ?string $description = null;

    /**
     * @var \Droath\ChatbotHub\Tools\ToolProperty[]
     */
    protected array $properties = [];

    /**
     * @param string $name
     */
    public function __construct(
        public readonly string $name,
    ) {}

    /**
     * @param string $name
     *
     * @return self
     */
    public static function make(string $name): self
    {
        return new self(Str::snake($name));
    }

    /**
     * @param mixed ...$args
     */
    public function __invoke(...$args): string
    {
        $function = $this->function;

        if (! isset($function)) {
            throw new \RuntimeException('Tool function is not defined.');
        }

        if (! is_callable($function)) {
            throw new \RuntimeException('Tool function is not callable.');
        }

        return $function($args);
    }

    /**
     * @return $this
     */
    public function using(\Closure $function): self
    {
        $this->function = $function;

        return $this;
    }

    /**
     * @return $this
     */
    public function strict(): self
    {
        $this->strict = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function describe(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param \Droath\ChatbotHub\Tools\ToolProperty[] $properties
     *
     * @return $this
     */
    public function withProperties(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasProperties(): bool
    {
        return ! empty($this->properties);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $properties = collect($this->properties);

        return [
            'name' => $this->name,
            'strict' => $this->strict,
            'description' => $this->description,
            'properties' => $properties,
            'required' => $properties
                ->filter(fn (ToolProperty $property) => $property->required)
                ->map(fn (ToolProperty $property) => $property->name)
                ->values()
                ->toArray(),
        ];
    }
}
