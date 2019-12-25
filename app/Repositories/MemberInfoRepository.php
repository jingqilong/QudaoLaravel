<?php


namespace App\Repositories;


use App\Models\MemberInfoModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberInfoRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param MemberInfoModel $model
     */
    public function __construct(MemberInfoModel $model)
    {
        $this->model = $model;
    }

    /**
     * 添加成员个人简历信息
     * @param $request
     * @param $member_id
     * @return bool|null
     */
    protected function addMemberInfo($request, $member_id)
    {
        $info_arr = [
            'member_id'         => $member_id,
            'grade'             => $request['grade'] ?? 0,
            'employer'          => $request['employer'] ?? '',
            'position'          => $request['position'] ?? '',
            'title'             => $request['title'] ?? 0,
            'industry'          => $request['category'] ?? 0,
            'brands'            => $request['brands'] ?? '',
            'run_wide'          => $request['run_wide'] ?? '',
            'category'          => $request['category'] ?? 0,
            'profile'           => $request['profile'] ?? '',
            'goodat'            => $request['goodat'] ?? '',
            'degree'            => $request['degree'] ?? '',
            'school'            => $request['school'] ?? '',
            'constellation'     => $request['constellation'] ?? '',
            'remarks'           => $request['remarks'] ?? '',
            'referral_agency'   => $request['referral_agency'] ?? '',
            'info_provider'     => $request['info_provider'] ?? '',
            'archive'           => $request['archive'] ?? 0,
            'is_recommend'      => $request['is_recommend'] ?? 0,
            'is_home_detail'    => $request['is_home_detail'] ?? 0,
            'created_at'        => time(),
            'update_at'         => time(),
        ];
        if ($this->exists($info_arr)){
            return false;
        }
        if (!$member_id = $this->getAddId($info_arr)){
            return false;
        }
        return $member_id;
    }
}
            