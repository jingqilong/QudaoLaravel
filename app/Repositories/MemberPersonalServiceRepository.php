<?php


namespace App\Repositories;


use App\Models\MemberPersonalServiceModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberPersonalServiceRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberPersonalServiceModel $model)
    {
        $this->model = $model;
    }


    /**
     * 成员服务信息服务
     * @param $request
     * @param $member_id
     * @return bool|null
     */
    protected function addMemberService($request, $member_id)
    {
        $service_arr = [
            'member_id'         => $member_id,
            'publicity'         => $request['publicity'] ?? 0,
            'protocol'          => $request['protocol'] ?? 0,
            'nameplate'         => $request['nameplate'] ?? 0,
            'attendant'         => $request['attendant'] ?? '',
            'member_attendant'  => $request['member_attendant'] ?? '',
            'gift'              => $request['gift'] ?? '',
            'other_server'      => $request['other_server'] ?? 1,
            'created_at'        => time(),
            'update_at'         => time(),
        ];
        if ($this->exists($service_arr)){
            return false;
        }
        if (!$member_id = $this->getAddId($service_arr)){
            return false;
        }
        return $member_id;
    }
}
            