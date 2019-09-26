<?php


namespace App\Repositories;


use App\Models\CommonSmsModel;
use App\Repositories\Traits\RepositoryTrait;

class CommonSmsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(CommonSmsModel $model)
    {
        $this->model = $model;
    }
}
            