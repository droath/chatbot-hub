<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Drivers;

use Droath\ChatbotHub\Drivers\Contracts\DriverInterface;
use Droath\ChatbotHub\Tools\Tool;

abstract class ChatbotHubDriver implements DriverInterface
{
    public static function transformTool(Tool $tool): array
    {
        return $tool->toArray();
    }
}
