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
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Models\ChatbotHubUserMessages;
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

    public array $chatMessages = [];

    #[Validate('required|min:3')]
    public ?string $chatMessage = null;

    public ?string $streamMessage = null;

    #[Locked]
    public ?ChatbotProvider $chatProvider = null;

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

    public function render(): View
    {
        return view(static::$view);
    }

    public function renderHtml(?string $content): string
    {
        return Str::sanitizeHtml(Str::markdown((string)$content));
    }

    protected function chatProvider(): ChatbotProvider
    {
        return $this->chatProvider;
    }

    protected function chatTools(): array
    {
        return [];
    }

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

    protected function setUserChatMessage(string $message): void
    {
        $this->chatMessageStorage()
            ->set(UserMessage::make($message))
            ->save();
    }

    protected function setAssistantChatMessage(string $message): void
    {
        $this->chatMessageStorage()
            ->set(AssistantMessage::make($message))
            ->save();
    }

    /**
     * Refresh the chat messages.
     */
    protected function refreshChatMessages(): void
    {
        $this->chatMessages = $this->chatMessageStorage()->toArray();

        $this->dispatch('chat-messages-refreshed');
    }

    protected function chatMessageStorage(): MessageStorageInterface
    {
        return new MessageDatabaseModelStorage(ChatbotHubUserMessages::class);
    }
}
