<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Drivers\Concerns;

trait HasStreaming
{
    /** @var bool */
    protected bool $stream = false;

    /** @var bool */
    protected bool $useStreamBuffer = false;

    /** @var string|null */
    protected ?string $streamBuffer = null;

    /** @var \Closure */
    protected \Closure $streamProcess;

    /** @var \Closure */
    protected \Closure $streamBufferProcess;

    /** @var \Closure|null */
    protected ?\Closure $streamFinished = null;

    /**
     * @param \Closure $streamProcess
     * @param \Closure|null $streamFinished
     *
     * @return $this
     */
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

    /**
     * @param \Closure $streamProcess
     * @param \Closure $streamBufferProcess
     * @param \Closure|null $streamFinished
     *
     * @return $this
     */
    public function usingStreamBuffer(
        \Closure $streamProcess,
        \Closure $streamBufferProcess,
        ?\Closure $streamFinished = null
    ): static
    {
        $this->stream = true;
        $this->useStreamBuffer = true;

        $this->streamProcess = $streamProcess;
        $this->streamFinished = $streamFinished;
        $this->streamBufferProcess = $streamBufferProcess;

        return $this;
    }
}
