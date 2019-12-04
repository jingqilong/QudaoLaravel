<?php


namespace App\Repositories;


use App\Models\CommonFeedBacksModel;
use App\Repositories\Traits\RepositoryTrait;

class CommonFeedBacksRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * CommonFeedBacksModel constructor.
     * @param $model
     */
    public function __construct(CommonFeedBacksModel $model)
    {
        $this->model = $model;
    }
}
            