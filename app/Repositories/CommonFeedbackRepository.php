<?php


namespace App\Repositories;


use App\Models\CommonFeedbackModel;
use App\Repositories\Traits\RepositoryTrait;

class CommonFeedbackRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(CommonFeedbackModel $model)
    {
        $this->model = $model;
    }
}
            