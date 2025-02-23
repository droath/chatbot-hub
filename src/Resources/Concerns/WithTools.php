<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources\Concerns;

use Illuminate\Support\Collection;

trait WithTools
{
    /**
     * @var array|\Illuminate\Support\Collection
     */
    protected array|Collection $tools = [];

    /**
     * {@inheritDoc}
     */
    public function withTools(array $tools): static
    {
        $this->tools = Collection::make($tools);

        return $this;
    }

    /**
     * Resolve the resource tools.
     *
     * @return array
     */
    protected function resolveTools(): array
    {
        if ($this->tools instanceof Collection) {
            return $this->tools->map(
                function ($tool) {
                    return $this->driver::transformTool($tool);
                }
            )->toArray();
        }

        return $this->tools;
    }
}
