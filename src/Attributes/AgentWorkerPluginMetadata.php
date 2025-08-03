<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Attributes;

use Droath\PluginManager\Attributes\PluginMetadata;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AgentWorkerPluginMetadata extends PluginMetadata
{
    /**
     * Define the attribute constructor.
     *
     * @param string $id
     *   The plugin identifier.
     * @param string $label
     *   The plugin human-readable name.
     */
    public function __construct(
        string $id,
        string $label,
        protected ChatbotProvider $provider,
        protected array $tools = []
    )
    {
        parent::__construct($id, $label);
    }
}
