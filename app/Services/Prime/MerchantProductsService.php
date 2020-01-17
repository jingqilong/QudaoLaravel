<?php
namespace App\Services\Prime;


use App\Enums\PrimeTypeEnum;
use App\Repositories\PrimeMerchantInfoRepository;
use App\Repositories\PrimeMerchantProductsRepository;
use App\Repositories\PrimeMerchantRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService as CommonImagesService;
use App\Traits\HelpTrait;

class MerchantProductsService extends BaseService
{
    use HelpTrait;

    /**
     * 添加产品
     * @param $request
     * @param $merchant_id
     * @param null $type
     * @return bool
     */
    public function addProduct($request, $merchant_id, $type = null)
    {
        if (!$merchant = PrimeMerchantInfoRepository::getOne(['merchant_id' => $merchant_id])){
            $this->setError('该商户不存在！');
            return false;
        }
        $add_arr = [
            'merchant_id'   => $merchant_id,
            'type'          => empty($type) ? $merchant['type'] : $type,
            'title'         => $request['title'],
            'describe'      => $request['describe'] ?? '',
            'price'         => $request['price'] * 100,
            'image_ids'     => $request['image_ids'],
            'is_recommend'  => isset($request['is_recommend']) ? ($request['is_recommend']==1 ? time() : 0) : 0
        ];
        if (PrimeMerchantProductsRepository::exists($add_arr)){
            $this->setError('该产品已添加！');
            return false;
        }
        $add_arr['created_at']  = time();
        $add_arr['updated_at']  = time();
        if (PrimeMerchantProductsRepository::getAddID($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }

    /**
     * 删除产品
     * @param $id
     * @param $merchant_id
     * @return bool
     */
    public function deleteProduct($id,$merchant_id = null){
        $where = ['id' => $id];
        if (!empty($merchant_id)){
            $where['merchant_id'] = $merchant_id;
        }
        if (!PrimeMerchantProductsRepository::exists($where)){
            $this->setError('该产品不存在！');
            return false;
        }
        if (!PrimeMerchantProductsRepository::delete($where)){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功！');
        return true;
    }

    /**
     * 修改产品
     * @param $request
     * @param null $merchant_id
     * @return bool
     */
    public function editProduct($request, $merchant_id = null)
    {
        $where = ['id' => $request['id']];
        if (!empty($merchant_id)){
            $where['merchant_id'] = $merchant_id;
        }
        $upd_arr = [
            'title'         => $request['title'],
            'describe'      => $request['describe'] ?? '',
            'price'         => $request['price'] * 100,
            'image_ids'     => $request['image_ids'],
            'is_recommend'  => isset($request['is_recommend']) ? ($request['is_recommend']==1 ? time() : 0) : 0
        ];
        if (PrimeMerchantProductsRepository::exists(array_merge($upd_arr,['merchant_id' =>['<>',$merchant_id]]))){
            $this->setError('该产品已添加！');
            return false;
        }
        $add_arr['updated_at']  = time();
        if (PrimeMerchantProductsRepository::getUpdID($where,$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取产品列表
     * @param $request
     * @param null $merchant_id
     * @return bool|mixed|null
     */
    public function productList($request,$merchant_id = null){
        $type       = $request['type'] ?? null;
        $is_recommend= $request['is_recommend'] ?? null;
        $keywords   = $request['keywords'] ?? null;
        $order      = 'id';
        $desc_asc   = 'desc';
        $where      = ['id' => ['>',0]];
        $column     = ['*'];
        if (!empty($merchant_id)){
            $where['merchant_id'] = $merchant_id;
        }
        if (!empty($type)){
            $where['type'] = $type;
        }
        if (!empty($is_recommend)){
            $order = 'is_recommend';
            $where['is_recommend'] = ($is_recommend==1) ? ['>',0] : 0;
        }
        if (!empty($keywords)){
            $keywords = [$keywords => ['title','describe']];
            if (!$list = PrimeMerchantProductsRepository::search($keywords,$where,$column,$order,$desc_asc)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = PrimeMerchantProductsRepository::getList($where,$column,$order,$desc_asc)){
                $this->setError('获取失败！');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $list['data'] = CommonImagesService::getListImages($list['data'], ['image_ids'=>'several']);
        $merchant_ids = array_column($list['data'],'merchant_id');
        $merchant_list= PrimeMerchantRepository::getAllList(['id' => ['in',$merchant_ids]],['id','name']);
        foreach ($list['data'] as &$value){
            $value['merchant_name'] = '';
            if ($merchant = $this->searchArray($merchant_list,'id',$value['merchant_id'])){
                $value['merchant_name'] = reset($merchant)['name'];
            }
            $value['type_title']    = PrimeTypeEnum::getType($value['type']);
            $value['price'] = empty($value['price']) ? '0' : round($value['price'] / 100,2);
            $value['is_recommend'] = empty($value['is_recommend']) ? 2 : 1;
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            