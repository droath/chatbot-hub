<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Messages;

use Droath\ChatbotHub\Enums\ChatbotRoles;
use Droath\ChatbotHub\Messages\Concerns\ViewSupport;
use Droath\ChatbotHub\Drivers\Contracts\DriverInterface;
use Droath\ChatbotHub\Messages\Contracts\MessageDriverAwareInterface;

/**
 * Define the user message value object.
 */
final class UserMessage extends MessageBase implements MessageDriverAwareInterface
{
    use ViewSupport;

    /**
     * @var \Droath\ChatbotHub\Drivers\Contracts\DriverInterface|null
     */
    protected ?DriverInterface $driver = null;

    /**
     * @param string $content
     * @param \Droath\ChatbotHub\Messages\MessageContext|null $context
     */
    private function __construct(
        public readonly string $content,
        public readonly ?MessageContext $context,
    ) {}

    /**
     * @param string $content
     * @param \Droath\ChatbotHub\Messages\MessageContext|string|null $context
     *
     * @return mixed
     */
    public static function make(
        string $content,
        null|string|MessageContext $context = null,
    ): UserMessage
    {
        return new self(
            $content,
            is_string($context)
                ? MessageContext::make($context)
                : $context
        );
    }

    /**
     * @inheritDoc
     */
    public static function fromLivewire($value): self
    {
        return self::fromValue($value);
    }

    /**
     * @inheritDoc
     */
    public static function fromValue(array $value): self
    {
        return self::make(
            $value['content'],
            is_array($value['context']) ? MessageContext::make(
                $value['context']['content'],
                $value['context']['metadata'] ?? []
            ) : $value['context']
        );
    }

    /**
     * @inheritDoc
     */
    public function toValue(): array
    {
        return [
            'role' => ChatbotRoles::USER->value,
            'content' => $this->content,
            'context' => $this->context,
        ];
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

        if (! empty($this->context->content)) {
            $content .= $this->context->content;
        }

        if ($this->driver instanceof DriverInterface) {
            return $this->driver::transformUserMessage(
                $content
            );
        }

        return $content;
    }
}
