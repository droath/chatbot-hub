<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Messages;

use Droath\ChatbotHub\Enums\ChatbotRoles;
use Droath\ChatbotHub\Drivers\Contracts\DriverInterface;
use Droath\ChatbotHub\Messages\Contracts\MessageDriverAwareInterface;

/**
 * Define the user message value object.
 */
final readonly class UserMessage extends MessageBase implements MessageDriverAwareInterface
{
    /**
     * @var \Droath\ChatbotHub\Drivers\Contracts\DriverInterface|null
     */
    protected ?DriverInterface $driver;

    /**
     * @param string $content
     * @param string|null $context
     */
    private function __construct(
        public string $content,
        public ?string $context,
    ) {}

    /**
     * @param string $content
     * @param string|null $context
     *
     * @return mixed
     */
    public static function make(
        string $content,
        ?string $context = null,
    ): UserMessage
    {
        return new self($content, $context);
    }

    /**
     * @inheritDoc
     */
    public static function fromLivewire($value): self
    {
        return self::make(
            $value['content'],
            $value['context']
        );
    }

    /**
     * @return bool
     */
    public function hasContext(): bool
    {
        return ! empty($this->context);
    }

    /**
     * @inheritDoc
     */
    public function toLivewire(): array
    {
        return [
            'content' => $this->content,
            'context' => $this->context,
        ];
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

        if (! empty($this->context)) {
            $content .= $this->context;
        }

        if ($this->driver instanceof DriverInterface) {
            return $this->driver::transformUserMessage(
                $content
            );
        }

        return $content;
    }
}
