<?php


namespace App\Repositories;


use App\Models\OaMessageSendModel;
use App\Repositories\Traits\RepositoryTrait;

class OaMessageSendRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaMessageSendModel $model)
    {
        $this->model = $model;
    }
}
            