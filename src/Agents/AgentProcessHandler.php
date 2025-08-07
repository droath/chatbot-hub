<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents;

use Illuminate\Support\Facades\Log;
use Droath\ChatbotHub\Facades\ChatbotHubAgent;

class AgentProcessHandler
{
    /** @var array|string */
    protected array|string $message = [];

    /**
     * @param string $name
     * @param \Closure $responseHandler
     */
    public function __construct(
        protected string $name,
        protected \Closure $responseHandler
    ) {}

    /**
     * @param array|string $message
     *
     * @return $this
     */
    public function message(array|string $message): static
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return mixed
     */
    public function __invoke(): mixed
    {
        try {
            if (
                $this->message
                && ($response = ChatbotHubAgent::run($this->name, $this->message))
            ) {
                return call_user_func($this->responseHandler, $response);
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }

        return null;
    }
}
