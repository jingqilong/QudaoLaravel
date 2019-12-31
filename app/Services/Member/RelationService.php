<?php
namespace App\Services\Member;


use App\Repositories\MemberBaseRepository;
use App\Repositories\MemberRelationRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Tolawho\Loggy\Facades\Loggy;

class RelationService extends BaseService
{
    /**
     * 创建推荐关系
     * @param $user_id
     * @param null $referral_code
     * @return bool
     */
    public function createdRelation($user_id, $referral_code = null){
        DB::beginTransaction();
        $relation_data['member_id']     = $user_id;
        $relation_data['created_at']    = time();
        $relation_data['updated_at']    = time();
        if (empty($referral_code)) {
            $relation_data['parent_id'] = 0;
            $relation_data['path']      = '0,' . $user_id . ',';
            $relation_data['level']     = 1;
        } else {
            if (!$referral_user = MemberBaseRepository::getOne(['referral_code' => $referral_code])) {
                DB::rollBack();
                $this->setError('无效的推荐码!');
                return false;
            }
            //如果推荐人没有推荐关系，给他创建
            if (!$relation_user = MemberRelationRepository::getOne(['member_id' => $referral_user['id']])) {
                if (false == $this->createdRelation($referral_user['id'])){
                    DB::rollBack();
                    Loggy::write('error', '给推荐人创建推荐关系失败，用户id：' . $user_id . '  推荐人id：' . $relation_user['id']);
                    $this->setError('创建推荐关系失败!');
                    return false;
                }
                $relation_user = MemberRelationRepository::getOne(['member_id' => $referral_user['id']]);
            }
            $relation_data['parent_id'] = $referral_user['id'];
            $relation_data['path']      = $relation_user['path'] . $user_id . ',';
            $relation_data['level']     = $relation_user['level'] + 1;
        }
        if (!MemberRelationRepository::getAddId($relation_data)) {
            DB::rollBack();
            Loggy::write('error', '推荐关系建立失败，用户id：' . $user_id . '  推荐人id：' . $relation_data['parent_id']);
            $this->setError('注册失败!');
            return false;
        }
        $this->setMessage('推荐关系创建成功！');
        DB::commit();
        return true;
    }

    /**
     * 更新推荐关系
     * @param $user_id
     * @param null $referral_code
     * @return bool
     */
    public function updateRelation($user_id, $referral_code = null){
        DB::beginTransaction();
        //如果当前用户还没有推荐关系，则去创建
        if (!$relation = MemberRelationRepository::getOne(['member_id' => $user_id])){
            return $this->createdRelation($user_id,$referral_code);
        }
        $relation_data['updated_at']    = time();
        if (empty($referral_code)) {
            $relation_data['parent_id'] = $relation['parent_id'];
            $relation_data['path']      = $relation['parent_id'];
            $relation_data['level']     = $relation['level'];
        } else {
            if (!$referral_user = MemberBaseRepository::getOne(['referral_code' => $referral_code])) {
                DB::rollBack();
                $this->setError('无效的推荐码!');
                return false;
            }
            //如果推荐人没有推荐关系，给他创建
            if (!$relation_user = MemberRelationRepository::getOne(['member_id' => $referral_user['id']])) {
                if (false == $this->createdRelation($referral_user['id'])){
                    DB::rollBack();
                    Loggy::write('error', '给推荐人创建推荐关系失败，用户id：' . $user_id . '  推荐人id：' . $relation_user['id']);
                    $this->setError('更新推荐关系失败!');
                    return false;
                }
            }
            $relation_data['parent_id'] = $referral_user['id'];
            $relation_data['path']      = $relation_user['path'] . $user_id . ',';
            $relation_data['level']     = $relation_user['level'] + 1;
        }
        if (!MemberRelationRepository::getUpdId(['id' => $relation['id']],$relation_data)) {
            DB::rollBack();
            Loggy::write('error', '推荐关系更新失败，用户id：' . $user_id . '  推荐人id：' . $relation_data['parent_id']);
            $this->setError('注册失败!');
            return false;
        }
        $this->setMessage('推荐关系更新成功！');
        DB::commit();
        return true;
    }
}
            