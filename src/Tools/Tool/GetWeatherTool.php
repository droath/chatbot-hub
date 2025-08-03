<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Tools\Tool;

use Droath\ChatbotHub\Tools\Tool;
use Droath\ChatbotHub\Tools\ToolProperty;

class GetWeatherTool extends Tool
{
    public function __construct()
    {
        parent::__construct('get_weather');
        $this->describe('The current weather for a particular location.');
        $this->using(function (array $arguments) {
            return "It's 89 degrees";
        });
        $this->withProperties([
            ToolProperty::make('location', 'string')->required(),
            ToolProperty::make('unit', 'string')
                ->describe('The city and state, e.g. San Francisco, CA')
                ->withEnums(['celsius', 'fahrenheit']),
        ]);
    }
}
