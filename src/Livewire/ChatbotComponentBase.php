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
use Droath\ChatbotHub\Messages\AssistantMessage;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
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
    public function sendMessage(): void
    {
        if ($message = $this->pull('message')) {
            if (empty($this->messages)) {
                $this->messages[] = $this->systemMessage();
            }
            $this->messages[] = UserMessage::make(
                $message,
                $this->userMessageContext($message)
            );
        }
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
            ->usingStream(function (string $partial, $initialized) {
                $this->stream(
                    'streamMessage',
                    $partial,
                    $initialized
                );
            }, function (ChatbotHubResponseMessage $response) {
                $this->messages[] = AssistantMessage::make($response->message);

                if ($this->parent instanceof Model) {
                    ChatbotMessages::updateOrCreate(
                        [
                            'user_id' => auth()->id(),
                            'parent_id' => $this->parent->id,
                            'parent_type' => $this->parent->getMorphClass(),
                        ],
                        [
                            'message' => $this->messages
                        ]
                    );
                }
            });

        $resource();
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
            ->where('parent_id', $this->parent->id)
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
     * @return string|null
     */
    protected function userMessageContext(string $message): ?string
    {
        return null;
    }
}
