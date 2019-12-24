<?php


namespace App\Repositories;


use App\Models\MemberPreferenceModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberPreferenceRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberPreferenceModel $model)
    {
        $this->model = $model;
    }

    /**
     * 添加成员偏好类型
     * @param $request
     * @param $member_id
     * @return bool|null
     */
    protected function addMemberPreference($request, $member_id)
    {
        $preference_arr = [
            'member_id'         => $member_id,
            'type'              => $request['type'],
            'content'           => $request['content'],
            'created_at'        => time(),
            'update_at'         => time(),
        ];
        if ($this->exists($preference_arr)){
            return false;
        }
        if (!$member_id = $this->getAddId($preference_arr)){
            return false;
        }
        return $member_id;
    }
}
            