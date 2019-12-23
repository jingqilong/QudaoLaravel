<?php


namespace App\Repositories;


use App\Models\OaGradeCollectModel;
use App\Repositories\Traits\RepositoryTrait;

class OaGradeViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * OaGradeViewRepository constructor.
     * @param OaGradeCollectModel $model
     */
    public function __construct(OaGradeCollectModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取成员可查看级别
     * @param null $where
     * @param array $column
     * @return array|bool
     */
    protected function showGrade($where = null, $column = ['*'])
    {
        if (!$grade = $this->getList($where,$column)){
            return false;
        }
        return array_column($grade,'value');
    }
}