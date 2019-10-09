<?php


namespace App\Repositories;


use App\Models\OaProcessActionRelatedModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessActionRelatedRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessActionRelatedModel $model)
    {
        $this->model = $model;
    }
}
            