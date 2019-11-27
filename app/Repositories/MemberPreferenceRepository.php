<?php


namespace App\Repositories;


use App\Models\MemberPreferenceModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberPreferenceRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberPreferenceModel $model)
    {
        $this->model = $model;
    }
}
            