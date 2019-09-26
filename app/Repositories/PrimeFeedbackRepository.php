<?php


namespace App\Repositories;


use App\Models\PrimeFeedbackModel;
use App\Repositories\Traits\RepositoryTrait;

class PrimeFeedbackRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(PrimeFeedbackModel $model)
    {
        $this->model = $model;
    }
}
            