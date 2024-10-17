<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Tools;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
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
     * @var ?\Closure
     */
    protected ?\Closure $function = null;

    /**
     * @var string
     */
    protected string $description;

    /**
     * @var \Droath\ChatbotHub\Tools\ToolProperty[]
     */
    protected array|Collection $properties = [];

    /**
     * @param string $name
     */
    public function __construct(
        public readonly string $name,
    )
    {
    }

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
     *
     * @return string
     */
    public function __invoke(...$args): string
    {
        $function = $this->function;

        if (!isset($function)) {
            throw new \RuntimeException('Tool function is not defined.');
        }

        if (!is_callable($function)) {
            throw new \RuntimeException('Tool function is not callable.');
        }

        return $function($args);
    }

    /**
     * @param \Closure $function
     *
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
     * @param \Droath\ChatbotHub\Tools\ToolProperty[] $properties
     *
     * @return $this
     */
    public function withProperties(array $properties): self
    {
        $this->properties = collect($properties);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'strict' => $this->strict,
            'description' => $this->description,
            'properties' => $this->properties,
            'required' => $this->properties
                ->filter(fn(ToolProperty $property) => $property->required)
                ->map(fn(ToolProperty $property) => $property->name)
                ->values()
                ->toArray(),
        ];
    }
}
