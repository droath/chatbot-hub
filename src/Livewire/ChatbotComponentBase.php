<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Livewire;

use Livewire\Component;
use Livewire\Attributes\Validate;
use Droath\ChatbotHub\Drivers\Openai;
use Illuminate\Database\Eloquent\Model;
use Droath\ChatbotHub\Facades\ChatbotHub;
use Illuminate\Database\Eloquent\Builder;
use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Messages\SystemMessage;
use Droath\ChatbotHub\Models\ChatbotMessages;
use Droath\ChatbotHub\Messages\MessageContext;
use Droath\ChatbotHub\Messages\AssistantMessage;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Resources\Contracts\HasToolsInterface;
use Droath\ChatbotHub\Livewire\Contracts\ChatbotComponentInterface;

abstract class ChatbotComponentBase extends Component implements ChatbotComponentInterface
{
    /** @var string */
    protected const string CHATBOT_DEFAULT_MODEL = Openai::DEFAULT_MODEL;

    /** @var \Droath\ChatbotHub\Drivers\Enums\ChatbotProvider */
    protected const ChatbotProvider CHATBOT_PROVIDER = ChatbotProvider::OPENAI;

    /** @var array */
    public array $messages = [];
    /** @var \Illuminate\Database\Eloquent\Model|null */
    public ?Model $parent = null;
    /** @var string|null */
    #[Validate('required|max:10000')]
    public ?string $message = null;
    /** @var bool */
    public bool $isStreaming = false;
    /** @var string|null */
    public ?string $streamMessage = null;

    /**
     * @return void
     */
    public function mount(): void
    {
        if ($this->parent instanceof Model) {
            $this->messages = $this->loadMessages();
        }
    }

    /**
     * @inheritDoc
     */
    public function sendMessage(): bool
    {
        if ($message = $this->pull('message')) {
            if (empty($this->messages)) {
                $this->messages[] = $this->systemMessage();
            }
            $context = $this->userMessageContext($message);

            $this->messages[] = UserMessage::make(
                $message,
                $context
            );

            return true;
        }

        return false;
    }

    /**
     * @return void
     */
    public function clearMessages(): void
    {
        $this->messages = [];

        if ($builder = $this->chatMessagesBuilder()) {
            $builder->delete();
        }
    }

    /**
     * @inheritDoc
     */
    public function respondToMessage(): void
    {
        $resource = ChatbotHub::chat(static::CHATBOT_PROVIDER)
            ->withModel(static::CHATBOT_DEFAULT_MODEL)
            ->withMessages($this->messages)
            ->usingStreamBuffer(
                function (string $partial, bool $initialized) {
                    $this->streamProcessing($partial, $initialized);
                },
                function (string $partial) {
                    return $this->streamBufferProcessing($partial);
                },
                function (ChatbotHubResponseMessage $response) {
                    $this->streamFinishedProcessing($response);
                }
            );

        if ($resource instanceof HasToolsInterface) {
            $resource->withTools($this->tools());
        }

        $resource();
    }

    /**
     * @return array
     */
    protected function tools(): array
    {
        return [];
    }

    /**
     * Handles the stream processing
     *
     * @param string $partial
     *   The partial stream data.
     * @param bool $initialized
     *   A flag denoting if the stream is initialized.
     *
     * @return void
     */
    protected function streamProcessing(
        string $partial,
        bool $initialized
    ): void
    {
        $this->stream(
            'streamMessage',
            $this->formatStreamData($partial),
            $initialized
        );
    }

    /**
     * Controls the processing of the stream buffer.
     *
     * Determines when the stream buffer should be processed to ensure
     * data is handled in consistent chunks rather than arbitrary fragments.
     *
     * @param string $partial
     *   The partial stream data to evaluate
     *
     * @return bool
     *   Whether the buffer should be processed
     */
    protected function streamBufferProcessing(
        string $partial
    ): bool
    {
        return true;
    }

    /**
     * Handles the stream finished processing.
     *
     * @param \Droath\ChatbotHub\Responses\ChatbotHubResponseMessage $response
     *   The chatbot hub response message.
     *
     * @return void
     */
    protected function streamFinishedProcessing(
        ChatbotHubResponseMessage $response
    ): void
    {
        $this->messages[] = AssistantMessage::make($response->message);

        if ($this->parent instanceof Model) {
            ChatbotMessages::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'parent_id' => $this->parent->getKey(),
                    'parent_type' => $this->parent->getMorphClass(),
                ],
                [
                    'message' => $this->messages
                ]
            );
        }
    }

    /**
     * Format the partial stream data.
     *
     * @param string $partial
     *   The partial stream data.
     *
     * @return string
     */
    protected function formatStreamData(string $partial): string
    {
        return $partial;
    }

    /**
     * Load the chatbot messages.
     */
    protected function loadMessages(): array
    {
        if ($builder = $this->chatMessagesBuilder()) {
            return $builder->value('message') ?? [];
        }

        return [];
    }

    /**
     * Define the chat message builder query.
     *
     * @return \Illuminate\Database\Eloquent\Builder|null
     */
    protected function chatMessagesBuilder(): ?Builder
    {
        if (! $this->parent instanceof Model) {
            return null;
        }

        return ChatbotMessages::where('user_id', auth()->id())
            ->where('parent_id', $this->parent->getKey())
            ->where('parent_type', $this->parent->getMorphClass());
    }

    /**
     * @return \Droath\ChatbotHub\Messages\SystemMessage
     */
    abstract protected function systemMessage(): SystemMessage;

    /**
     * Define user message context based on the current chatbot message.
     *
     * @param string $message
     *
     * @return \Droath\ChatbotHub\Messages\MessageContext|null
     */
    protected function userMessageContext(string $message): ?MessageContext
    {
        return null;
    }
}
