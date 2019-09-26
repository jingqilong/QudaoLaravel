<?php


namespace App\Repositories;


use App\Models\PrimeAuditModel;
use App\Repositories\Traits\RepositoryTrait;

class PrimeAuditRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(PrimeAuditModel $model)
    {
        $this->model = $model;
    }
}
            