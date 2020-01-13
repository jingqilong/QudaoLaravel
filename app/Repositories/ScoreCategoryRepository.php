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

/**
 * <这是为IDE添加自动完成的以及调试所用的注解>
 *
 * @method        int           PRESTIGE()                                                                               Get the Activety typr id.
 * @method        int           BONUS()                                                                                  Get the Activety typr id.
 * @method        int           GOLD_COIN()                                                                             Get the Activety typr id.
 *
 * <end 自动完成>
 *
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
            