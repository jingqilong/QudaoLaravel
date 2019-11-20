<?php


namespace App\Repositories;


use App\Models\MessageDefModel;
use App\Repositories\Traits\RepositoryTrait;

class MessageDefRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MessageDefModel $model)
    {
        $this->model = $model;
    }
}
            