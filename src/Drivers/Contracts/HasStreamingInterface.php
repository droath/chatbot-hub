<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Drivers\Contracts;

interface HasStreamingInterface
{
    /**
     * Use the resource streaming.
     *
     *
     * @return $this
     */
    public function usingStream(
        \Closure $streamProcess,
        ?\Closure $streamFinished = null
    ): static;
}
