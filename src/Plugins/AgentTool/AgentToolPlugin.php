<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins\AgentTool;

use Droath\ChatbotHub\Plugins\Contracts\AgentToolPluginInterface;
use Droath\ChatbotHub\Tools\Tool;
use Droath\PluginManager\Plugin\PluginBase;

/**
 * Define the agent tool plugin base class.
 */
abstract class AgentToolPlugin extends PluginBase implements AgentToolPluginInterface
{
    protected Tool $tool;

    public function __construct(
        array $configuration,
        array $pluginDefinition
    ) {
        parent::__construct($configuration, $pluginDefinition);

        $this->tool = Tool::make(
            $this->getPluginId()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function definition(): Tool
    {
        return $this->tool
            ->using(function (array $arguments) {
                return $this->execute($arguments);
            })->withProperties($this->properties());
    }

    abstract protected function properties(): array;

    abstract protected function execute(array $arguments): mixed;
}
