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
     * 添加成员偏好类型
     * @param $request
     * @param $member_id
     * @return bool|null
     */
    protected function addMemberPreference($request, $member_id)
    {
        $preference = json_encode($request['preference']);
        $preference_arr = [];
        foreach ($preference as $value){
            $preference_arr = [
                'member_id'         => $member_id,
                'type'              => $value['type'],
                'content'           => $value['content'],
                'created_at'        => time(),
                'update_at'         => time(),
            ];
        }
        DB::beginTransaction();
        $this->delete(['member_id' => $member_id]);
        DB::rollBack();
        if (!$this->create($preference_arr)){
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * 获取会员的偏好属性
     * @param $member_id
     * @return array
     */
    protected function getPreference($member_id)
    {
        if (!$preference  = $this->getList(['member_id' => $member_id],['id','type','content'])){
            return [];
        }
        $preference_str = '';
        foreach($preference as $key=>$val) $preference_str.=$val['content'].',';
        return explode(',',rtrim($preference_str,','));
    }
}
            