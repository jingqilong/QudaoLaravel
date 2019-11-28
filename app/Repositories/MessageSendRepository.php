<?php


namespace App\Repositories;


use App\Models\MessageSendModel;
use App\Repositories\Traits\RepositoryTrait;

class MessageSendRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MessageSendModel $model)
    {
        $this->model = $model;
    }
}
            