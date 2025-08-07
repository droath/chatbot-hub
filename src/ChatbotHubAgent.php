<?php

declare(strict_types=1);

namespace Droath\ChatbotHub;

use Illuminate\Support\Facades\Log;
use Droath\ChatbotHub\Plugins\AgentWorkerPluginManager;

class ChatbotHubAgent
{
    /**
     * @param string $pluginId
     * @param string|array $message
     *
     * @return mixed
     */
    public function run(
        string $pluginId,
        string|array $message,
    ): array
    {
        try {
            $manager = app(AgentWorkerPluginManager::class);
            /** @var \Droath\ChatbotHub\Plugins\Contracts\AgentWorkerPluginInterface $agent */
            if ($agent = $manager->createInstance($pluginId)) {
                return $agent->respond(
                    is_array($message) ? $message : [$message]
                );
            }
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
        }

        return [];
    }
}
