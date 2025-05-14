<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Casts;

use Illuminate\Database\Eloquent\Model;
use Droath\ChatbotHub\Enums\ChatbotRoles;
use Droath\ChatbotHub\Messages\MessageBase;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class AsChatbotMessage implements Castable
{
    /**
     * @inheritDoc
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes {

            /**
             * @inheritDoc
             */
            public function get(
                Model $model,
                string $key,
                mixed $value,
                array $attributes
            ): array
            {
                $storedValue = Json::decode($value);

                return collect($storedValue)
                    ->filter(fn ($message): bool => isset($message['role']))
                    ->map(function (array $message): MessageBase {
                        $role = $message['role'] ?? null;
                        unset($message['role']);
                        return ChatbotRoles::createMessageFrom(
                            $role,
                            $message
                        );
                    })
                    ->all();
            }

            /**
             * @inheritDoc
             */
            public function set(
                Model $model,
                string $key,
                mixed $value,
                array $attributes
            ): array
            {
                $storedValue = collect($value)
                    ->map(function (MessageBase $message) {
                        return $message->toValue();
                    })->toArray();

                return [$key => Json::encode($storedValue)];
            }
        };
    }
}

