<?php


namespace App\Repositories;


use App\Enums\AdminMenuEnum;
use App\Models\OaAdminMenuModel;
use App\Repositories\Traits\RepositoryTrait;
use phpDocumentor\Reflection\Types\Integer;

class OaAdminMenuRepository extends ApiRepository
{
    use RepositoryTrait;

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
            'type'      => $add_data['type'],
            'parent_id' => $add_data['parent_id'],
            'path'      => $add_data['path'],
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
    protected function getMenuList(array $where, $column = ['id', 'type', 'parent_id', 'path', 'level', 'title', 'icon', 'method', 'url']){
        if (!$list = $this->getList($where,$column,'order','asc')){
            return [];
        }
        $parent_ids = array_column($list,'parent_id');
        $menu_list = [];
        //查询填补上级菜单
        foreach ($list as &$value){
            $http = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
            $value['url'] = $http . $_SERVER['HTTP_HOST'] . '/api/v1/' . $value['path'];
            if (!in_array($value['parent_id'],$parent_ids)){
                $menu_list[$value['parent_id']] = OaAdminMenuRepository::getOne(['id' => $value['parent_id']],$column);
                $parent_ids += [$value['parent_id']];
                continue;
            }
            $menu_list[$value['id']] = $value;
        }
        //处理菜单结构
        $res    = [];
        foreach ($menu_list as $id => $v){
            if ($v['parent_id'] == 0){
//                $res[$id] = $v;
//                $res[$id]['next_level'] = $this->levelPartition($menu_list,$id);
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
            