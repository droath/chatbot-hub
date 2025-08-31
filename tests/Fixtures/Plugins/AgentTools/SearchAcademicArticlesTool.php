<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Tests\Fixtures\Plugins\AgentTools;

use Droath\ChatbotHub\Tools\ToolProperty;
use Droath\ChatbotHub\Plugins\AgentTool\AgentToolPlugin;
use Droath\ChatbotHub\Attributes\AgentToolPluginMetadata;

#[AgentToolPluginMetadata(
    id: 'search_academic_articles',
    label: 'Search Academic Articles',
    description: 'Search the latest academic articles.',
)]
class SearchAcademicArticlesTool extends AgentToolPlugin
{
    /**
     * {@inheritDoc}
     */
    public function execute(array $arguments): string
    {
        return 'Here is the latest information around your search query';
    }

    /**
     * {@inheritDoc}
     */
    public function properties(): array
    {
        return [
            ToolProperty::make('query', 'string')->required(),
        ];
    }
}
