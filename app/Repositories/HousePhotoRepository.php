<?php


namespace App\Repositories;


use App\Models\HousePhotoModel;
use App\Repositories\Traits\RepositoryTrait;

class HousePhotoRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(HousePhotoModel $model)
    {
        $this->model = $model;
    }
}
            