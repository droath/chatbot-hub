<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources\Contracts;

use Droath\ChatbotHub\Drivers\Contracts\DriverInterface;

interface HasDriverInterface
{
    /**
     * Get the driver instance.
     *
     * @return \Droath\ChatbotHub\Drivers\Contracts\DriverInterface
     */
    public function driver(): DriverInterface;
}
