<?php


namespace App\Repositories;


use App\Models\MemberRelationModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberRelationRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberRelationModel $model)
    {
        $this->model = $model;
    }

    /**
     * 只获取直接推荐和间接推荐关系
     * @param $user_id
     * @return array|bool|null
     */
    protected function doubleRelation($user_id){
        if (!$path = $this->getField(['member_id' => $user_id],'path')){
            return false;
        }
        $relation_column = ['member_id', 'parent_id', 'path', 'level'];
        $member_column = ['m_id', 'm_cname', 'm_groupname', 'm_phone'];
        //获取直接推荐人
        if (!$direct_relation = $this->getAllList(['path' => ['like', $path.',%'],'parent_id' => $user_id],$relation_column)){
            return [];
        }
        $direct_ids = array_column($direct_relation,'member_id');
        $direct_users = OaMemberRepository::getAllList(['m_id' => ['in' , $direct_ids]],$member_column);
        //获取间接推荐人
        foreach ($direct_relation as &$v){
            $path = $v['path'];
            foreach ($direct_users as $user){
                if ($v['member_id'] == $user['m_id']){
                    $v = $user;
                    break;
                }
            }
            if (!$indirect_users = $this->getAllList(['path' => ['like' , $path.',%']],$relation_column)){
                $v['next_level'] = [];
                continue;
            }
            $indirect_ids = array_column($indirect_users,'member_id');
            $v['next_level'] = OaMemberRepository::getAllList(['m_id' => ['in' , $indirect_ids]],$member_column);
        }
        return $direct_relation;
    }

    /**
     * 获取详细推荐关系
     * @param $user_id
     * @return array|bool
     */
    protected function detailRelation($user_id){
        if (!$path = $this->getField(['member_id' => $user_id],'path')){
            return false;
        }
        $relation_column = ['member_id', 'parent_id', 'path', 'level'];
        $member_column = ['m_id', 'm_cname', 'm_groupname', 'm_phone'];
        if (!$relation_list = $this->getAllList(['path' => ['like', $path.',%']],$relation_column)){
            return [];
        }
        $direct_ids = array_column($relation_list,'member_id');
        $all_users = OaMemberRepository::getAllList(['m_id' => ['in',$direct_ids]],$member_column);
        foreach ($relation_list as &$v){
            foreach ($all_users as $user){
                if ($v['member_id'] == $user['m_id']){
                    $v += $user;
                }
            }
        }//dd($relation_list);
        $list = $this->levelPartition($relation_list,$user_id);
        return $list;
    }

    /**
     * @param $array
     * @param $parent_id
     * @return array
     */
    function  levelPartition($array, $parent_id){
        $res = [];
        foreach ($array as $v){
            if ($v['parent_id'] == $parent_id){
                $res[] = array_merge($v,['next_level' => $this->levelPartition($array,$v['member_id'])]);
                continue;
            }
        }
        return $res;
    }
}
            