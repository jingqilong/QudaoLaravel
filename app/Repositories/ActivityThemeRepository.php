<?php


namespace App\Repositories;


use App\Models\ActivityThemeModel;
use App\Repositories\Traits\RepositoryTrait;

class ActivityThemeRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ActivityThemeModel $model)
    {
        $this->model = $model;
    }
}
            