<?php


namespace App\Repositories;


use App\Models\CommonCommentsModel;
use App\Repositories\Traits\RepositoryTrait;

class CommonCommentsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(CommonCommentsModel $model)
    {
        $this->model = $model;
    }
}
            