<?php


namespace App\Repositories;


use App\Models\CommonFeedBacksViewModel;
use App\Repositories\Traits\RepositoryTrait;

class CommonFeedBacksViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * CommonFeedBacksViewModel constructor.
     * @param $model
     */
    public function __construct(CommonFeedBacksViewModel $model)
    {
        $this->model = $model;
    }
}
            