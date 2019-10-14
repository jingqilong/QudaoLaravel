<?php


namespace App\Repositories;


use App\Models\ProjectModel;
use App\Repositories\Traits\RepositoryTrait;

class ProjectRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ProjectModel $model)
    {
        $this->model = $model;
    }
}
            