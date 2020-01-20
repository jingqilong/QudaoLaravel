<?php


namespace App\Repositories;


use App\Models\CommonFeedbackThreadModel;
use App\Repositories\Traits\RepositoryTrait;

class CommonFeedbackThreadRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(CommonFeedbackThreadModel $model)
    {
        $this->model = $model;
    }
}
            