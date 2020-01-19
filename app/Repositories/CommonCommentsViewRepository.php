<?php


namespace App\Repositories;


use App\Models\CommonCommentsViewModel;
use App\Repositories\Traits\RepositoryTrait;

class CommonCommentsViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(CommonCommentsViewModel $model)
    {
        $this->model = $model;
    }


}
            