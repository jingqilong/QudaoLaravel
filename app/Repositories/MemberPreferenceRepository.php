<?php


namespace App\Repositories;


use App\Models\MemberPreferenceModel;
use App\Repositories\Traits\RepositoryTrait;
use Illuminate\Support\Facades\DB;

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
     * 获取会员的偏好属性
     * @param $member_id
     * @return array
     */
    protected function getPreference($member_id)
    {
        if (!$preference  = $this->getList(['member_id' => $member_id],['id','content'])){
            return [];
        }
        $preference_str = '';
        foreach($preference as $key=>$val) $preference_str.=$val['content'].',';
        return explode(',',rtrim($preference_str,','));
    }
}
            