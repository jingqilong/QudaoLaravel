<?php


namespace App\Repositories;


use App\Models\ActivityThemeModel;
use App\Repositories\Traits\RepositoryTrait;

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
            