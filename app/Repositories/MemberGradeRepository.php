<?php


namespace App\Repositories;


use App\Enums\MemberEnum;
use App\Models\MemberGradeModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberGradeRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberGradeModel $model)
    {
        $this->model = $model;
    }

    protected function getAddGrade(int $user_id)
    {
        $add_arr = [
            'user_id'    => $user_id,
            'grade'      => MemberEnum::DEFAULT,
            'status'     => MemberEnum::PASS,
            'created_at' => time(),
            'update_at'  => time(),
        ];
        $this->getAddId($add_arr);
        return '普通成员';
    }
}
            