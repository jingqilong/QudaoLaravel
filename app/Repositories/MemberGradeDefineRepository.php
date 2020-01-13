<?php


namespace App\Repositories;


use App\Models\MemberGradeDefineModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberGradeDefineRepository extends EnumerableRepository
{
    use RepositoryTrait;

    protected $columns_map = ['id'=>'id','label'=>'title','name'=>'tag'];

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberGradeDefineModel $model)
    {
        $this->model = $model;
    }
}
            