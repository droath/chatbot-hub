<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources\Concerns;

trait WithInput
{
    /** @var string|array */
    protected string|array $input = '';

    /**
     * @param string|array $input
     *
     * @return $this
     */
    public function withInput(string|array $input): static
    {
        $this->input = $input;

        return $this;
    }
}
