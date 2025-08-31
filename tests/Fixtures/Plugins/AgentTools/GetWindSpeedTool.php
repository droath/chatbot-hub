<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Tests\Fixtures\Plugins\AgentTools;

use Droath\ChatbotHub\Tools\ToolProperty;
use Droath\ChatbotHub\Plugins\AgentTool\AgentToolPlugin;
use Droath\ChatbotHub\Attributes\AgentToolPluginMetadata;

#[AgentToolPluginMetadata(
    id: 'get_wind_speed',
    label: 'Get Wind Speed',
    description: 'Get the wind speed for a given location.',
)]
class GetWindSpeedTool extends AgentToolPlugin
{
    /**
     * {@inheritDoc}
     */
    public function execute(array $arguments): string
    {
        return "The wind speed is 10 miles per hour in {$arguments['location']} today!";
    }

    /**
     * {@inheritDoc}
     */
    public function properties(): array
    {
        return [
            ToolProperty::make('location', 'string')->required(),
        ];
    }
}
