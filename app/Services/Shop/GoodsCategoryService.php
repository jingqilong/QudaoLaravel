<?php
namespace App\Services\Shop;


use App\Repositories\ShopGoodsCategoryRepository;
use App\Repositories\ShopGoodsRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
use App\Traits\HelpTrait;

class GoodsCategoryService extends BaseService
{
    use HelpTrait;

    /**
     * 添加类别
     * @param $request
     * @return bool
     */
    public function addCategory($request)
    {
        if (ShopGoodsCategoryRepository::exists(['name' => $request['name']])){
            $this->setError('类别名称已被使用！');
            return false;
        }
        $add_arr = [
            'name'      => $request['name'],
            'icon_id'   => $request['icon_id'],
            'created_at'=> time(),
            'updated_at'=> time(),
        ];
        if (ShopGoodsCategoryRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }

    /**
     * 删除类别
     * @param $id
     * @return bool
     */
    public function deleteCategory($id)
    {
        if (!ShopGoodsCategoryRepository::exists(['id' => $id])){
            $this->setError('类别不存在！');
            return false;
        }
        if (ShopGoodsRepository::exists(['category' => $id])){
            if (!ShopGoodsCategoryRepository::delete(['id' => $id])){
                $this->setError('删除失败！');
                return false;
            }
        }else{
            if (!ShopGoodsCategoryRepository::getUpdId(['id' => $id],['deleted_at' => time()])){
                $this->setError('删除失败！');
                return false;
            }
        }
        $this->setMessage('添加成功！');
        return true;
    }

    /**
     * 编辑商品类别
     * @param $request
     * @return bool
     */
    public function editCategory($request)
    {
        if (!ShopGoodsCategoryRepository::exists(['id' => $request['id']])){
            $this->setError('类别不存在！');
            return false;
        }
        if (ShopGoodsCategoryRepository::exists(['id' => ['<>',$request['id']],'name' => $request['name']])){
            $this->setError('类别名称已被使用！');
            return false;
        }
        $upd_arr = [
            'name'      => $request['name'],
            'icon_id'   => $request['icon_id'],
            'updated_at'=> time(),
        ];
        if (ShopGoodsCategoryRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取商品类别
     * @param $request
     * @return bool|mixed|null
     */
    public function getCategoryList($request)
    {
        $page = $request['page'] ?? 1;
        $page_num = $request['page_num'] ?? 20;
        $keywords = $request['keywords'] ?? null;
        $where = ['deleted_at' => 0];
        $column = ['id','name','icon_id','created_at','updated_at'];
        if (!empty($keywords)){
            if (!$list = ShopGoodsCategoryRepository::search([$keywords => ['name']],$where,$column,$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = ShopGoodsCategoryRepository::getList($where,$column,null,null,$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $list['data'] = ImagesService::getListImages($list['data'],['icon_id' => 'single']);
        $this->setMessage('获取成功！');
        return $list;
    }
}
            