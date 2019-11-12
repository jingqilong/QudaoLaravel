<?php
namespace App\Services\Shop;


use App\Repositories\ShopAnnounceRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;

class AnnounceService extends BaseService
{
    use HelpTrait;

    /**
     * 添加公告
     * @param $request
     * @return bool
     */
    public function addAnnounce($request)
    {
        if (!ShopAnnounceRepository::getAddId(['content' => $request['content'],'created_at' => time()])){
            $this->setError('添加失败！');
            return false;
        }
        $this->setMessage('添加成功！');
        return true;
    }

    /**
     * 删除公告
     * @param $id
     * @return bool
     */
    public function deleteAnnounce($id)
    {
        if (!ShopAnnounceRepository::exists(['id' => $id])){
            $this->setError('公告信息不存在！');
            return false;
        }
        if (!ShopAnnounceRepository::delete(['id' => $id])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功！');
        return true;
    }

    /**
     * 编辑公告
     * @param $request
     * @return bool
     */
    public function editAnnounce($request)
    {
        if (!ShopAnnounceRepository::exists(['id' => $request['id']])){
            $this->setError('公告信息不存在！');
            return false;
        }
        if (!ShopAnnounceRepository::getUpdId(['id' => $request['id']],['content' => $request['content']])){
            $this->setError('修改失败！');
            return false;
        }
        $this->setMessage('修改成功！');
        return true;
    }

    /**
     * 获取公告列表
     * @param $request
     * @return bool|mixed|null
     */
    public function getAnnounceList($request)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $keywords   = $request['keywords'] ?? null;
        $where      = ['id' => ['>',0]];
        $order      = 'id';
        $desc_asc   = 'desc';
        $column     = ['*'];
        if (!empty($keywords)){
            if (!$list = ShopAnnounceRepository::search([$keywords => ['content']],$where,$column,$page,$page_num,$order,$desc_asc)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = ShopAnnounceRepository::getList($where,$column,$order,$desc_asc,$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            