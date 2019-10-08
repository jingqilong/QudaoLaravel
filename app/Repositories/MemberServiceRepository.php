<?php


namespace App\Repositories;


use App\Models\MemberServiceModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberServiceRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberServiceModel $model)
    {
        $this->model = $model;
    }


    /**
     * 获取服务列表
     * @param array $where
     * @param array $column
     * @return array
     */
    protected function getServiceList(array $where, $column = ['id', 'name', 'desc', 'path', 'level','parent_id']){
        if (!$list = $this->getList($where,$column)){
            return [];
        }
        $parent_ids = array_column($list,'parent_id');
        $service_list = [];
        //查询填补上级菜单
        foreach ($list as &$value){
            if (!in_array($value['parent_id'],$parent_ids)){
                $service_list[$value['parent_id']] = $this->getOne(['id' => $value['parent_id']],$column);
                $parent_ids += [$value['parent_id']];
                continue;
            }
            $service_list[$value['id']] = $value;
        }
        //处理菜单结构
        $res    = [];
        foreach ($service_list as $id => $v){
            if ($v['parent_id'] == 0){
                unset($v['path'],$v['level'],$v['parent_id']);
                $res[] = array_merge($v,['next_level' => $this->levelPartition($service_list,$id)]);
            }
        }
        return $res;
    }

    /**
     * @param $array
     * @param $parent_id
     * @return array
     */
    function  levelPartition($array, $parent_id){
        $res = [];
        foreach ($array as $id => $v){
            if ($v['parent_id'] == $parent_id){
                unset($v['path'],$v['level'],$v['parent_id']);
                $res[] = array_merge($v,['next_level' => $this->levelPartition($array,$id)]);
                continue;
            }
        }
        return $res;
    }


    /**
     * @param array $where
     * @return array|null
     */
    protected function getDetail(array $where)
    {
        if (!$info = $this->getOne($where)){
            return [];
        }
        $info['next_level'] = [];
        $info['created_at'] = date('Y-m-d H:m:s', $info['created_at']);
        $info['updated_at'] = date('Y-m-d H:m:s', $info['updated_at']);
        if ($next_list = $this->getList(['parent_id' => $info['id']])){//dd($next_list);
            $info['next_level'] = $this->levelDetail($next_list,$info['id']);
        }
        unset($info['path'],$info['level']);
        return $info;
    }

    /**
     * @param $array
     * @param $parent_id
     * @return array
     */
    function  levelDetail($array, $parent_id){
        $res = [];
        foreach ($array as &$v){
            if ($v['parent_id'] == $parent_id){
                unset($v['path'],$v['level']);
                $v['created_at'] = date('Y-m-d H:m:s', $v['created_at']);
                $v['updated_at'] = date('Y-m-d H:m:s', $v['updated_at']);
                $res[] = array_merge($v,['next_level' => $this->levelDetail($array,$v['id'])]);
                continue;
            }
        }
        return $res;
    }
}
            