<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins\Contracts;

use Droath\ChatbotHub\Tools\Tool;

/**
 * Define the agent tool plugin interface.
 */
interface AgentToolPluginInterface
{
    /**
     * The tool plugin definition.
     */
    public function definition(): Tool;
}
