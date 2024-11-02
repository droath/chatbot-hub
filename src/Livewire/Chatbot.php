<?php

namespace Droath\ChatbotHub\Livewire;

use Livewire\Component;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;
use Droath\ChatbotHub\Facades\ChatbotHub;
use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Messages\AssistantMessage;
use Droath\ChatbotHub\Models\ChatbotHubUserMessages;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;
use Droath\ChatbotHub\Messages\Contracts\MessageStorageInterface;
use Droath\ChatbotHub\Messages\Storage\MessageDatabaseModelStorage;

/**
 * Define the chatbot livewire component.
 */
class Chatbot extends Component
{
    protected static string $view = 'chatbot-hub::livewire.chatbot';

    /**
     * @var array
     */
    public array $chatMessages = [];

    /**
     * @var string|null
     */
    #[Validate('required|min:3')]
    public ?string $chatMessage = null;

    /**
     * @var string|null
     */
    public ?string $streamMessage = null;

    /**
     * @var \Droath\ChatbotHub\Drivers\Enums\ChatbotProvider|null
     */
    #[Locked]
    public ?ChatbotProvider $chatProvider = null;

    /**
     * @param object|null $provider
     *
     * @return void
     */
    public function mount(
        ?object $provider = null
    ): void
    {
        $provider = $provider ?? ChatbotProvider::OPENAI;

        if (isset($provider) && ! $provider instanceof ChatbotProvider) {
            throw new \InvalidArgumentException(
                'Provider must be an instance of
                Droath\ChatbotHub\Drivers\Enums\ChatbotProvider.'
            );
        }
        $this->chatProvider = $provider;
        $this->chatMessages = $this->chatMessageStorage()->toArray();
    }

    /**
     * Send the chat message to the chatbot provider.
     *
     * @return void
     */
    public function sendMessage(): void
    {
        $this->validate();

        try {
            if ($message = $this->pull('chatMessage')) {
                $this->setUserChatMessage($message);
                $this->refreshChatMessages();
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    /**
     * Response to the chat message using the chat resource.
     *
     * @return void
     */
    public function respondToMessage(): void
    {
        try {
            if ($resource = $this->chatResource()) {
                $resource();
                $this->refreshChatMessages();
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    /**
     * @return \Illuminate\View\View
     */
    public function render(): View
    {
        return view(static::$view);
    }

    /**
     * @param string|null $content
     *
     * @return string
     */
    public function renderHtml(?string $content): string
    {
        return Str::sanitizeHtml(Str::markdown((string)$content));
    }

    /**
     * @return \Droath\ChatbotHub\Drivers\Enums\ChatbotProvider
     */
    protected function chatProvider(): ChatbotProvider
    {
        return $this->chatProvider;
    }

    /**
     * @return array
     */
    protected function chatTools(): array
    {
        return [];
    }

    /**
     * @return \Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface
     */
    protected function chatResource(): ChatResourceInterface
    {
        return ChatbotHub::chat($this->chatProvider())
            ->withTools($this->chatTools())
            ->withMessages($this->chatMessageStorage())
            ->usingStream(function (string $chunk, $initialized) {
                $this->stream(
                    'streamMessage',
                    $chunk,
                    $initialized
                );
                $this->refreshChatMessages();
            }, function (ChatbotHubResponseMessage $response) {
                $this->setAssistantChatMessage(
                    $response->message
                );
            });
    }

    /**
     * @param string $message
     *
     * @return void
     */
    protected function setUserChatMessage(string $message): void
    {
        $this->chatMessageStorage()
            ->set(UserMessage::make($message))
            ->save();
    }

    /**
     * @param string $message
     *
     * @return void
     */
    protected function setAssistantChatMessage(string $message): void
    {
        $this->chatMessageStorage()
            ->set(AssistantMessage::make($message))
            ->save();
    }

    /**
     * Refresh the chat messages.
     *
     * @return void
     */
    protected function refreshChatMessages(): void
    {
        $this->chatMessages = $this->chatMessageStorage()->toArray();

        $this->dispatch('chat-messages-refreshed');
    }

    /**
     * @return \Droath\ChatbotHub\Messages\Contracts\MessageStorageInterface
     */
    protected function chatMessageStorage(): MessageStorageInterface
    {
        return new MessageDatabaseModelStorage(ChatbotHubUserMessages::class);
    }
}
