<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Messages\Contracts;

use Droath\ChatbotHub\Drivers\Contracts\DriverInterface;

interface MessageDriverAwareInterface
{
    /**
     * @param \Droath\ChatbotHub\Drivers\Contracts\DriverInterface $driver
     *
     * @return void
     */
    public function setDriver(DriverInterface $driver): void;
}
