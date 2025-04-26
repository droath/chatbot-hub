<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Drivers\Contracts;

use Droath\ChatbotHub\Tools\Tool;

interface DriverInterface
{
    /**
     * Transform driver tools to their specified format.
     *
     * @param \Droath\ChatbotHub\Tools\Tool $tool
     *
     * @return array
     */
    public static function transformTool(Tool $tool): array;

    /**
     * Transform the driver user message to their specified format.
     *
     * @param string $content
     *
     * @return string|array
     */
    public static function transformUserMessage(string $content): string|array;
}
