<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Messages;

use Droath\ChatbotHub\Enums\ChatbotRoles;
use Illuminate\Contracts\Support\Arrayable;
use Droath\ChatbotHub\Drivers\Contracts\DriverInterface;
use Droath\ChatbotHub\Messages\Contracts\MessageDriverAwareInterface;

/**
 * Define the user message value object.
 */
final readonly class UserMessage implements Arrayable, MessageDriverAwareInterface
{
    /**
     * @var \Droath\ChatbotHub\Drivers\Contracts\DriverInterface|null
     */
    protected ?DriverInterface $driver;

    private function __construct(
        public string $content,
    ) {}

    public static function make(string $content): self
    {
        return new self($content);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'role' => ChatbotRoles::USER->value,
            'content' => $this->structureContent(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function setDriver(DriverInterface $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * @return string|array
     */
    protected function structureContent(): string|array
    {
        $content = $this->content;

        if ($this->driver instanceof DriverInterface) {
            return $this->driver::transformUserMessage(
                $content
            );
        }

        return $content;
    }
}
