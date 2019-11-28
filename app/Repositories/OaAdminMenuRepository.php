<?php


namespace App\Repositories;


use App\Enums\AdminMenuEnum;
use App\Models\OaAdminMenuModel;
use App\Repositories\Traits\RepositoryTrait;
use App\Traits\HelpTrait;
use phpDocumentor\Reflection\Types\Integer;

class OaAdminMenuRepository extends ApiRepository
{
    use RepositoryTrait,HelpTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaAdminMenuModel $model)
    {
        $this->model = $model;
    }

    /**
     * 添加一个菜单
     * @param array $add_data
     * @return integer|null
     */
    protected function createMenu(array $add_data){
        $arr = [
            'primary_key' => $add_data['primary_key'],
            'type'      => $add_data['type'],
            'parent_id' => $add_data['parent_id'],
            'path'      => $add_data['path'],
            'vue_route' => $add_data['vue_route'],
            'level'     => $add_data['level'],
            'order'     => $add_data['order'] ?? 0,
            'title'     => $add_data['title'],
            'icon'      => $add_data['icon'],
            'method'    => $add_data['method'],
            'url'       => $add_data['url'] ?? '',
            'permission'=> $add_data['permission'] ?? '',
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s'),
        ];
        return $this->getAddId($arr);
    }

    /**
     * 获取菜单列表
     * @param array $where
     * @param array $column
     * @return array
     */
    protected function getMenuList(array $where, $column = ['id', 'primary_key', 'type', 'parent_id', 'path','vue_route', 'level', 'title', 'icon', 'method', 'url']){
        if (!$list = $this->getList($where,$column,'order','asc')){
            return [];
        }
        $parent_ids = array_unique(array_column($list,'parent_id'));
        $menu_ids   = array_column($list,'id');
        $parent_list= $this->getList(['id' => ['in',array_diff($parent_ids,$menu_ids)]],$column,'order','asc');
        $menu_list = [];
        //查询填补上级菜单
        foreach ($list as &$value){
            if ($parent = $this->searchArray($parent_list,'id',$value['parent_id'])){
                $menu_list[$value['parent_id']] = reset($parent);
            }
            $menu_list[$value['id']] = $value;
        }
        //处理菜单结构
        $res    = [];
        foreach ($menu_list as $id => $v){
            if ($v['parent_id'] == 0){
                $res[] = array_merge($v,['next_level' => $this->levelPartition($menu_list,$id)]);
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
//                $res[$id] = $v;
//                $res[$id]['next_level'] = $this->levelPartition($array,$id);
                $res[] = array_merge($v,['next_level' => $this->levelPartition($array,$id)]);
                continue;
            }
        }
        return $res;
    }
}
            