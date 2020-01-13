<?php


namespace App\Repositories;


use App\Models\ScoreCategoryModel;
use App\Repositories\Traits\RepositoryTrait;

/**
 * @desc 继承可枚举类EnumerableRepository，使其可以当枚举使用
 * 参见父类中的注解
 * Class ScoreCategoryRepository
 * @package App\Repositories
 */
class ScoreCategoryRepository extends EnumerableRepository
{
    use RepositoryTrait;

    protected $columns_map = ['id'=>'id','label'=>'name','name'=>'tag'];

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ScoreCategoryModel $model)
    {
        $this->model = $model;
    }
}
            