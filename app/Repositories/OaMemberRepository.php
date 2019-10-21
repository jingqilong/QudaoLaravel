<?php


namespace App\Repositories;


use App\Models\MemberModel;
use App\Repositories\Traits\RepositoryTrait;

class OaMemberRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * OaMemberRepository constructor.
     * @param MemberModel $model
     */
    public function __construct(MemberModel $model)
    {
        $this->model = $model;
    }

}
            