<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Livewire;

use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Drivers\Openai;
use Droath\ChatbotHub\Facades\ChatbotHub;
use Droath\ChatbotHub\Livewire\Contracts\ChatbotComponentInterface;
use Droath\ChatbotHub\Messages\AssistantMessage;
use Droath\ChatbotHub\Messages\MessageContext;
use Droath\ChatbotHub\Messages\SystemMessage;
use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Models\ChatbotMessages;
use Droath\ChatbotHub\Resources\Contracts\HasToolsInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Validate;
use Livewire\Component;

abstract class ChatbotComponentBase extends Component implements ChatbotComponentInterface
{
    /** @var string */
    protected const string CHATBOT_DEFAULT_MODEL = Openai::DEFAULT_MODEL;

    /** @var \Droath\ChatbotHub\Drivers\Enums\ChatbotProvider */
    protected const ChatbotProvider CHATBOT_PROVIDER = ChatbotProvider::OPENAI;

    public array $messages = [];

    public ?Model $parent = null;

    #[Validate('required|max:10000')]
    public ?string $message = null;

    public bool $isStreaming = false;

    public ?string $streamMessage = null;

    public function mount(): void
    {
        if ($this->parent instanceof Model) {
            $this->messages = $this->loadMessages();
        }
    }

    /**
     * {@inheritDoc}
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

    public function clearMessages(): void
    {
        $this->messages = [];

        if ($builder = $this->chatMessagesBuilder()) {
            $builder->delete();
        }
    }

    /**
     * {@inheritDoc}
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
     */
    protected function streamProcessing(
        string $partial,
        bool $initialized
    ): void {
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
    ): bool {
        return true;
    }

    /**
     * Handles the stream finished processing.
     *
     * @param \Droath\ChatbotHub\Responses\ChatbotHubResponseMessage $response
     *   The chatbot hub response message.
     */
    protected function streamFinishedProcessing(
        ChatbotHubResponseMessage $response
    ): void {
        $this->messages[] = AssistantMessage::make($response->message);

        if ($this->parent instanceof Model) {
            ChatbotMessages::updateOrCreate(
                [
                    'owner_id' => auth()->id(),
                    'owner_type' => auth()->user()->getMorphClass(),
                    'parent_id' => $this->parent->getKey(),
                    'parent_type' => $this->parent->getMorphClass(),
                ],
                [
                    'message' => $this->messages,
                ]
            );
        }
    }

    /**
     * Format the partial stream data.
     *
     * @param string $partial
     *   The partial stream data.
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
     */
    protected function chatMessagesBuilder(): ?Builder
    {
        if (! $this->parent instanceof Model) {
            return null;
        }

        return ChatbotMessages::where('owner_id', auth()->id())
            ->where('owner_type', auth()->user()->getMorphClass())
            ->where('parent_id', $this->parent->getKey())
            ->where('parent_type', $this->parent->getMorphClass());
    }

    abstract protected function systemMessage(): SystemMessage;

    /**
     * Define user message context based on the current chatbot message.
     */
    protected function userMessageContext(string $message): ?MessageContext
    {
        return null;
    }
}
