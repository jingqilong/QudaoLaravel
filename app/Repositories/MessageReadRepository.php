<?php


namespace App\Repositories;


use App\Models\MessageReadModel;
use App\Repositories\Traits\RepositoryTrait;

class MessageReadRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MessageReadModel $model)
    {
        $this->model = $model;
    }
}
            