<?php


namespace App\Repositories;


use App\Models\ActivityCommentKeywordsModel;
use App\Repositories\Traits\RepositoryTrait;

class ActivityCommentKeywordsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ActivityCommentKeywordsModel $model)
    {
        $this->model = $model;
    }
}
            