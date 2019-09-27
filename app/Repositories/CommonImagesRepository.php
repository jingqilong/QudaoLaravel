<?php


namespace App\Repositories;


use App\Models\CommonImagesModel;
use App\Repositories\Traits\RepositoryTrait;

class CommonImagesRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(CommonImagesModel $model)
    {
        $this->model = $model;
    }
}
            