{{
    Vite::useHotFile('vendor/chatbot-hub/chatbot-hub/chatbot-hub.hot')
        ->useBuildDirectory('vendor/chatbot-hub')
        ->withEntryPoints(['resources/css/index.css'])
}}

<div
    x-init="
        scrollToBottom()
        $wire.on('chat-messages-refreshed', () => {
            scrollToBottom()
        })
    "
    x-data="{
        streaming: false,
        scrollToBottom() {
            $nextTick(() => {
                const scrollElement = document.scrollingElement || document.body
                window.scrollTo({
                    top: scrollElement.scrollHeight,
                    behavior: 'smooth',
                })
            })
        },
    }"
    class="flex flex-col"
>
    <ul class="flex-1">
        @if (! empty($chatMessages))
            @foreach ($chatMessages as $chatMessage)
                @php
                    $isUser = $chatMessage['role'] === 'user';
                @endphp

                <x-chatbot-hub::chatbot-message-bubble
                    position="{{ $isUser ? 'right' : 'left' }}"
                    icon="{{ $isUser ? 'heroicon-o-user': 'heroicon-o-bolt' }}"
                >
                    {!! $this->renderHtml($chatMessage['content']) !!}
                </x-chatbot-hub::chatbot-message-bubble>
            @endforeach
        @endif

        <template x-if="streaming">
            <x-chatbot-hub::chatbot-message-bubble
                position="left"
                icon="heroicon-o-cpu-chip"
            >
                <span wire:loading>
                    <svg
                        class="mr-3 h-5 w-5 animate-spin text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>
                </span>
                <span wire:stream="streamMessage">
                    {{ $streamMessage }}
                </span>
            </x-chatbot-hub::chatbot-message-bubble>
        </template>
    </ul>
    <div class="sticky bottom-0 p-4 shadow-sm">
        <form
            x-data="{
                sendChatMessage() {
                    streaming = true
                    $wire.sendMessage().then(() => {
                        $wire.respondToMessage().then(() => {
                            streaming = false
                        })
                    })
                },
            }"
            x-on:submit.prevent="sendChatMessage()"
        >
            <div
                class="flex rounded-lg bg-gray-700 p-2 shadow-sm shadow-gray-500"
            >
                <textarea
                    rows="1"
                    required
                    wire:model="chatMessage"
                    class="mr-4 w-full rounded-lg p-2 text-gray-900"
                    placeholder="{{ __('chatbot-hub::translations.chatbot.textarea.placeholder') }}"
                    @keydown.meta.enter="sendChatMessage"
                ></textarea>

                <button
                    type="submit"
                    class="rounded bg-blue-500 p-2 text-white hover:bg-blue-600"
                >
                    {{ __('chatbot-hub::translations.chatbot.submit.label') }}
                </button>
            </div>
        </form>
    </div>
</div>
