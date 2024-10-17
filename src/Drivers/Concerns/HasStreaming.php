<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Drivers\Concerns;

trait HasStreaming
{
    protected bool $stream = false;

    protected \Closure $streamProcess;

    protected ?\Closure $streamFinished = null;

    public function usingStream(
        \Closure $streamProcess,
        ?\Closure $streamFinished = null
    ): static
    {
        $this->stream = true;
        $this->streamProcess = $streamProcess;
        $this->streamFinished = $streamFinished;

        return $this;
    }
}
