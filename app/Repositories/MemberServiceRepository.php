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
     * 获取服务树列表
     * @param array $where
     * @param array $column
     * @return array
     */
    protected function getServiceList(array $where, $column = ['id', 'name', 'desc', 'path', 'level','parent_id']){
        if (!$list = $this->getAllList($where,$column)){
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
        $all_service_list = $this->getAllIndexService();
        //处理菜单结构
        $res    = [];
        foreach ($service_list as $id => $v){
            $v['parent_name'] = $v['name'];
            if ($v['parent_id'] == 0){
                unset($v['path'],$v['level'],$v['parent_id']);
                $res[] = array_merge($v,['next_level' => $this->levelPartition($service_list,$id,$all_service_list)]);
            }
        }
        return $res;
    }

    /**
     * @param $array
     * @param $parent_id
     * @return array
     */
    function  levelPartition($array, $parent_id,$all_service_list){
        $res = [];
        foreach ($array as $id => $v){
            $v['parent_name'] = isset($all_service_list[$v['id']]) ? $all_service_list[$v['id']]['name'] : $v['name'];
            if ($v['parent_id'] == $parent_id){
                unset($v['path'],$v['level'],$v['parent_id']);
                $res[] = array_merge($v,['next_level' => $this->levelPartition($array,$id,$all_service_list)]);
                continue;
            }
        }
        return $res;
    }


    /**
     * 获取服务详情
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
        if ($next_list = $this->getAllList(['parent_id' => $info['id']])){//dd($next_list);
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

    /**
     * 获取所有建立好索引的服务列表
     * @param array $column
     * @return array|null
     */
    protected function getAllIndexService($column = []){
        if (!$service_list = MemberServiceRepository::getAll(array_merge(['id','name','parent_id'],$column))){
            return [];
        }
        $service_list = createArrayIndex($service_list,'id');
        foreach ($service_list as &$value){
            if (!empty($value['parent_id'])){
                $value['name'] = $this->getParentServiceName($service_list,$value['parent_id']) . '-' . $value['name'];
            }
        }
        return $service_list;
    }

    /**
     * 获取父级服务名称
     * @param $array
     * @param $parent_id
     * @return string
     */
    function getParentServiceName ($array, $parent_id){
        if (!isset($array[$parent_id])){
            return '';
        }
        $parent = $array[$parent_id];
        $parent_name = $parent['name'];
        if (!empty($parent['parent_id'])){
            $parent_name = $this->getParentServiceName($array,$parent['parent_id']) . '-' . $parent_name;
        }
        return $parent_name;
    }
}
            