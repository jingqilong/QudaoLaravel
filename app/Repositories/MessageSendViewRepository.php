<?php


namespace App\Repositories;


use App\Models\MessageSendViewModel;
use App\Repositories\Traits\RepositoryTrait;

class MessageSendViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MessageSendViewModel $model)
    {
        $this->model = $model;
    }
}
            