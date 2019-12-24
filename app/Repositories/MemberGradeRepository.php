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

    /**
     * 添加成员等级
     * @param $request
     * @param $member_id
     * @return bool|null
     */
    protected function addMemberGrade($request, $member_id)
    {
        $grade_arr = [
            'user_id'           => $member_id,
            'grade'             => $request['grade'],
            'status'            => MemberEnum::PASS,
            'end_at'            => strtotime('+' . $request['end_at'] . 'year'),
            'created_at'        => time(),
            'update_at'         => time(),
        ];
        if ($this->exists($grade_arr)){
            return false;
        }
        if (!$member_id = $this->getAddId($grade_arr)){
            return false;
        }
        return $member_id;
    }
}
            