<?php


namespace App\Repositories;


use App\Models\CommonServiceTermsModel;
use App\Repositories\Traits\RepositoryTrait;

class CommonServiceTermsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(CommonServiceTermsModel $model)
    {
        $this->model = $model;
    }

}
            