<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Tests\Fixtures\Plugins\AgentTools;

use Droath\ChatbotHub\Tools\ToolProperty;
use Droath\ChatbotHub\Plugins\AgentTool\AgentToolPlugin;
use Droath\ChatbotHub\Attributes\AgentToolPluginMetadata;

#[AgentToolPluginMetadata(
    id: 'get_weather',
    label: 'Get Weather',
    description: 'Get the weather for a given location.',
)]
class GetWeatherTool extends AgentToolPlugin
{
    /**
     * {@inheritDoc}
     */
    public function execute(array $arguments): string
    {
        return "It's 59 degrees {$arguments['unit']} in {$arguments['location']} today!";
    }

    /**
     * {@inheritDoc}
     */
    public function properties(): array
    {
        return [
            ToolProperty::make('location', 'string')->required(),
            ToolProperty::make('unit', 'string')
                ->describe('The city and state, e.g. San Francisco, CA')
                ->withEnums(['celsius', 'fahrenheit']),
        ];
    }
}
