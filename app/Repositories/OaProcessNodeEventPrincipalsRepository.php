<?php


namespace App\Repositories;

use App\Models\OaProcessNodeEventPrincipalsModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessNodeEventPrincipalsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessNodeEventPrincipalsModel $model)
    {
        $this->model = $model;
    }
}