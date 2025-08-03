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
     * @return \Droath\ChatbotHub\Tools\Tool
     */
    public function definition(): Tool;
}
