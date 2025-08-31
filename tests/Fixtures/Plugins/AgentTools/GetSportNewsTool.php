<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Tests\Fixtures\Plugins\AgentTools;

use Droath\ChatbotHub\Tools\ToolProperty;
use Droath\ChatbotHub\Plugins\AgentTool\AgentToolPlugin;
use Droath\ChatbotHub\Attributes\AgentToolPluginMetadata;

#[AgentToolPluginMetadata(
    id: 'get_sports_news',
    label: 'Get Sport News',
    description: 'Get the latest sports news.',
)]
class GetSportNewsTool extends AgentToolPlugin
{
    /**
     * {@inheritDoc}
     */
    public function execute(array $arguments): string
    {
        return 'The Denver Broncos are playing in the NFL game today!';
    }

    /**
     * {@inheritDoc}
     */
    public function properties(): array
    {
        return [
            ToolProperty::make('team', 'string')->required(),
        ];
    }
}
