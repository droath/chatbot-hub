<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins\AgentTool;

use Droath\ChatbotHub\Tools\Tool;
use Droath\PluginManager\Plugin\PluginBase;
use Droath\ChatbotHub\Plugins\Contracts\AgentToolPluginInterface;

/**
 * Define the agent tool plugin base class.
 */
abstract class AgentToolPlugin extends PluginBase implements AgentToolPluginInterface
{
    /** @var \Droath\ChatbotHub\Tools\Tool */
    protected Tool $tool;

    /**
     * @param array $configuration
     * @param array $pluginDefinition
     */
    public function __construct(
        array $configuration,
        array $pluginDefinition
    )
    {
        parent::__construct($configuration, $pluginDefinition);

        $this->tool = Tool::make(
            $this->getPluginId()
        );
    }

    /**
     * @inheritDoc
     */
    public function definition(): Tool
    {
        return $this->tool
            ->using(function (array $arguments) {
                return $this->execute($arguments);
            })->withProperties($this->properties());
    }

    /**
     * @return array
     */
    abstract protected function properties(): array;

    /**
     * @param array $arguments
     *
     * @return mixed
     */
    abstract protected function execute(array $arguments): mixed;
}
