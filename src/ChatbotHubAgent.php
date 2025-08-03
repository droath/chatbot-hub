<?php

declare(strict_types=1);

namespace Droath\ChatbotHub;

use Illuminate\Support\Facades\Log;
use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Plugins\AgentWorkerPluginManager;

class ChatbotHubAgent
{
    /**
     * @param string $pluginId
     * @param string $instruction
     *
     * @return mixed
     */
    public function run(
        string $pluginId,
        string $instruction
    ): mixed
    {
        try {
            $manager = app(AgentWorkerPluginManager::class);
            /** @var \Droath\ChatbotHub\Plugins\Contracts\AgentWorkerPluginInterface $agent */
            if ($agent = $manager->createInstance($pluginId)) {
                return $agent->response([UserMessage::make($instruction)]);
            }
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
        }

        return null;
    }
}
