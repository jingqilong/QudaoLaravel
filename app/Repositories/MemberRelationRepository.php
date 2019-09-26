<?php


namespace App\Repositories;


use App\Models\MemberRelationModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberRelationRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberRelationModel $model)
    {
        $this->model = $model;
    }
}
            