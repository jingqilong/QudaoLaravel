<?php


namespace App\Repositories;


use App\Models\MemberPreferenceTypeModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberPreferenceTypeRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * MemberPreferenceTypeModel constructor.
     * @param $model
     */
    public function __construct(MemberPreferenceTypeModel $model)
    {
        $this->model = $model;
    }
}
            