<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Messages\Concerns;

use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

trait ViewSupport
{
    /**
     * @param \Illuminate\View\View $view
     *
     * @return self
     */
    public static function fromView(View $view): self
    {
        try {
            return self::make($view->render());
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
        }

        return self::make('');
    }
}
