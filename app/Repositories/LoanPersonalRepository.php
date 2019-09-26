<?php


namespace App\Repositories;


use App\Models\LoanPersonalModel;
use App\Repositories\Traits\RepositoryTrait;

class LoanPersonalRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(LoanPersonalModel $model)
    {
        $this->model = $model;
    }
}
            