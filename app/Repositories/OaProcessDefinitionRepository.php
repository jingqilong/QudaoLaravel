<?php


namespace App\Repositories;


use App\Models\OaProcessDefinitionModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessDefinitionRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessDefinitionModel $model)
    {
        $this->model = $model;
    }
}
            