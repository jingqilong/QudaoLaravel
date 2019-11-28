<?php


namespace App\Repositories;


use App\Models\MessageCategoryModel;
use App\Repositories\Traits\RepositoryTrait;

class MessageCategoryRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MessageCategoryModel $model)
    {
        $this->model = $model;
    }
}
            