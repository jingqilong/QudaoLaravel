<?php


namespace App\Repositories;


use App\Models\MemberPreferenceValueModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberPreferenceValueRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * MemberPreferenceTypeModel constructor.
     * @param $model
     */
    public function __construct(MemberPreferenceValueModel $model)
    {
        $this->model = $model;
    }
}
            