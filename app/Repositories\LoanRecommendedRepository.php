<?php


namespace App\Repositories;


use App\Models\LoanRecommendedModel;
use App\Repositories\Traits\RepositoryTrait;

class LoanRecommendedRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(LoanRecommendedModel $model)
    {
        $this->model = $model;
    }
}
            