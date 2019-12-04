<?php


namespace App\Repositories;


use App\Models\CommonFeedbacksModel;
use App\Repositories\Traits\RepositoryTrait;

class CommonFeedbacksRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(CommonFeedbacksModel $model)
    {
        $this->model = $model;
    }
}
            