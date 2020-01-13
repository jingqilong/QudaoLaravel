<?php


namespace App\Repositories;


use App\Models\ActivityThemeModel;
use App\Repositories\Traits\RepositoryTrait;

/**
 * <这是为IDE添加自动完成的以及调试所用的注解>
 *
 * @method        int           WINEPARTY()                                                                               Get the Activety typr id.
 * @method        int           FORUM()                                                                                  Get the Activety typr id.
 * @method        int           SALOON()                                                                             Get the Activety typr id.
 * @method        int           GIFTS()                                                                               Get the Activety typr id.
 *
 * <end 自动完成>
 *
 */
class ActivityThemeRepository extends EnumerableRepository
{
    use RepositoryTrait;

    protected $columns_map = ['id'=>'id','label'=>'name','name'=>'tag'];

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ActivityThemeModel $model)
    {
        $this->model = $model;
    }
}
            