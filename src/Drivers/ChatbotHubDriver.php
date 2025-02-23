<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Drivers;

use Droath\ChatbotHub\Tools\Tool;
use Droath\ChatbotHub\Drivers\Contracts\DriverInterface;

abstract class ChatbotHubDriver implements DriverInterface
{
    public static function transformTool(Tool $tool): array
    {
        return $tool->toArray();
    }
}
