<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Messages\Storage;

use Illuminate\Database\Eloquent\Model;

class MessageDatabaseModelStorage extends MessageGenericStorage
{
    protected Model $model;

    public function __construct(
        string $modelClassname,
    ) {
        if (! is_subclass_of($modelClassname, Model::class)) {
            throw new \RuntimeException(
                'Model class must be an instance of
                Illuminate\Database\Eloquent\Model.'
            );
        }
        $this->model = app($modelClassname);

        $userChats = $this->model::authUser()->first();

        parent::__construct(
            $userChats ? ($userChats->messages ?? []) : []
        );
    }

    /**
     * {@inheritDoc}
     */
    public function save(): void
    {
        $model = $this->model;

        if (
            $model->isFillable('user_id')
            && $model->isFillable('messages')
            && $model->hasCast('messages', 'json')
        ) {
            $model::upsert([
                'user_id' => Auth()->id(),
                'messages' => $this->messages->toJson(),
            ], ['user_id'], ['messages']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete(): void
    {
        $this->model::authUser()->delete();
    }
}
