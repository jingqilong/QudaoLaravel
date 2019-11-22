<?php


namespace App\Repositories;


use App\Models\OaAdminPermissionsModel;
use App\Repositories\Traits\RepositoryTrait;

class OaAdminPermissionsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaAdminPermissionsModel $model)
    {
        $this->model = $model;
    }

    /**
     * 添加权限
     * @param $add_data
     * @return integer|null
     */
    protected function createPermission($add_data)
    {
        $arr = [
            'name'          => $add_data['name'],
            'slug'          => $add_data['slug'],
            'http_method'   => $add_data['http_method'] ?? '',
            'http_path'     => $add_data['http_path'] ?? '',
            'created_at'    => date('Y-m-d H:m:s'),
            'updated_at'    => date('Y-m-d H:m:s')
        ];
        return $this->getAddId($arr);
    }
    /**
     * 修改权限
     * @param $upd_data
     * @return integer|null
     */
    protected function updatePermission($id,array $upd_data)
    {
        $arr = [
            'name'          => $upd_data['name'],
            'http_method'   => $upd_data['http_method'] ?? '',
            'http_path'     => $upd_data['http_path'] ?? '',
            'updated_at'    => date('Y-m-d H:m:s')
        ];
        return $this->getUpdId(['id' => $id],$arr);
    }
}
            