<?php


namespace App\Repositories;


use App\Models\OaProcessActionPrincipalsModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessActionPrincipalsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessActionPrincipalsModel $model)
    {
        $this->model = $model;
    }
}
            