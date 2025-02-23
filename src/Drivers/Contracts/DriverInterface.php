<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Drivers\Contracts;

use Droath\ChatbotHub\Tools\Tool;

interface DriverInterface
{
    /**
     * @param \Droath\ChatbotHub\Tools\Tool $tool
     *
     * @return array
     */
    public static function transformTool(Tool $tool): array;
}
