<?php
namespace App\Services\Shop;


use App\Enums\CommentsEnum;
use App\Enums\CommonHomeEnum;
use App\Enums\ShopGoodsEnum;
use App\Models\MemberGradeViewModel;
use App\Repositories\CommonCommentsRepository;
use App\Repositories\CommonImagesRepository;
use App\Repositories\MemberCollectRepository;
use App\Repositories\ShopGoodsCategoryRepository;
use App\Repositories\ShopGoodsRepository;
use App\Repositories\ShopGoodsSpecRelateRepository;
use App\Repositories\ShopGoodsSpecRepository;
use App\Repositories\ShopOrderGoodsRepository;
use App\Services\BaseService;
use App\Services\Common\HomeBannersService;
use App\Services\Common\ImagesService;
use App\Traits\HelpTrait;
use Encore\Admin\Grid\Model;
use Illuminate\Support\Facades\DB;

class GoodsService extends BaseService
{
    use HelpTrait;

    /**
     * 添加商品
     * @param $request
     * @return bool
     */
    public function addGoods($request)
    {
        #商品规格模板
//                $spec = [
//            ['stock' => 58,'price' => 680,
//                'spec' => [
//                    ['spec_name' => '规格','spec_value' => '智启 0.5g*6片','image_id' => '131'],
//                ]
//            ],
//            ['stock' => 58,'price' => 2680,
//                'spec' => [
//                    ['spec_name' => '规格','spec_value' => '智创 0.5g*24片','image_id' => '131'],
//                ]
//            ],
//        ];
        if (!$category = ShopGoodsCategoryRepository::getOne(['id' => $request['category']])){
            $this->setError('商品分类不存在！');
            return false;
        }
        if (isset($request['score_deduction']) && !isset($request['score_categories'])){
            $this->setError('可抵扣积分类型不能为空！');
            return false;
        }
        $add_arr = [
            'name'              => $request['name'],
            'category'          => $request['category'],
            'price'             => $request['price'] * 100,
            'details'           => $request['details'] ?? '',
            'labels'            => isset($request['labels']) ? ','.$request['labels'].',' : '',
            'banner_ids'        => $request['banner_ids'],
            'image_ids'         => $request['image_ids'],
            'stock'             => $request['stock'],
            'express_price'     => isset($request['express_price']) ? $request['express_price'] * 100 : 0,
            'score_deduction'   => $request['score_deduction'] ?? 0,
            'score_categories'  => $request['score_categories'] ?? '',
            'gift_score'        => $request['gift_score'] ?? 0,
            'status'            => $request['status'],
        ];
        if (ShopGoodsRepository::exists($add_arr)){
            $this->setError('该商品已添加！');
            return false;
        }
        $add_arr['keywords']        = $request['name'].$category['name'].$request['labels'];
        $add_arr['is_recommend']    = isset($request['is_recommend']) ? ($request['is_recommend'] == 1 ? time() : 0) : 0;
        $add_arr['created_at']      = $add_arr['updated_at'] = time();
        DB::beginTransaction();
        if (!$goods_id = ShopGoodsRepository::getAddId($add_arr)){
            DB::rollBack();
            $this->setError('添加失败！');
            return false;
        }
        #处理规格
        if (isset($request['spec_json']) && !empty($request['spec_json'])){
            $goodsSpecService = new GoodsSpecService();
            if (!$goodsSpecService->addJsonSpec($goods_id,$request['spec_json'],$add_arr['keywords'])){
                DB::rollBack();
                $this->setError($goodsSpecService->error);
                return false;
            }
        }
        DB::commit();
        $this->setMessage('添加成功！');
        return true;
    }

    /**
     * 删除商品
     * @param $id
     * @return bool
     */
    public function deleteGoods($id){
        if (!ShopGoodsRepository::exists(['id' => $id,'deleted_at' => 0])){
            $this->setError('商品信息不存在！');
            return false;
        }
        if (!ShopGoodsRepository::getUpdId(['id' => $id],['deleted_at' => time()])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功！');
        return true;
    }

    /**
     * 上下架商品
     * @param $id
     * @return bool
     */
    public function isPutaway($id){
        if (!$goods = ShopGoodsRepository::getOne(['id' => $id])){
            $this->setError('商品信息不存在！');
            return false;
        }
        if ($goods['status'] == ShopGoodsEnum::PUTAWAY){
            if (ShopGoodsRepository::getUpdId(['id' => $id],['status' => ShopGoodsEnum::UNSHELVE])){
                $this->setMessage('下架成功！');
                return true;
            }
        }else{
            if (ShopGoodsRepository::getUpdId(['id' => $id],['status' => ShopGoodsEnum::PUTAWAY])){
                $this->setMessage('上架成功！');
                return true;
            }
        }
        $this->setError('操作失败！');
        return false;
    }


    /**
     * 编辑商品
     * @param $request
     * @return bool
     */
    public function editGoods($request){
        if (!$goods = ShopGoodsRepository::getOne(['id' => $request['id']])){
            $this->setError('商品信息不存在！');
            return false;
        }
        if (!$category = ShopGoodsCategoryRepository::getOne(['id' => $request['category']])){
            $this->setError('商品分类不存在！');
            return false;
        }
        if (isset($request['score_deduction']) && !isset($request['score_categories'])){
            $this->setError('可抵扣积分类型不能为空！');
            return false;
        }
        $upd_arr = [
            'name'              => $request['name'],
            'category'          => $request['category'],
            'price'             => $request['price'] * 100,
            'details'           => $request['details'] ?? '',
            'labels'            => isset($request['labels']) ? ','.$request['labels'].',' : '',
            'banner_ids'        => $request['banner_ids'],
            'image_ids'         => $request['image_ids'],
            'stock'             => $request['stock'],
            'express_price'     => isset($request['stock']) ? $request['stock'] * 100 : 0,
            'score_deduction'   => $request['score_deduction'] ?? 0,
            'score_categories'  => $request['score_categories'] ?? '',
            'gift_score'        => $request['gift_score'] ?? 0,
            'status'            => $request['status'],
        ];
        if (ShopGoodsRepository::exists($upd_arr)){
            $this->setError('该商品已添加！');
            return false;
        }
        $upd_arr['keywords']        = $request['name'].$category['name'].$request['labels'];
        $upd_arr['is_recommend']    = isset($request['is_recommend']) ? ($request['is_recommend'] == 1 ? time() : 0) : 0;
        $upd_arr['updated_at']      = time();
        DB::beginTransaction();
        if (!$goods_id = ShopGoodsRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            DB::rollBack();
            $this->setError('修改失败！');
            return false;
        }
        #处理规格
        if (isset($request['spec_json'])){
            $goodsSpecService = new GoodsSpecService();
            if (!$goodsSpecService->addJsonSpec($goods_id,$request['spec_json'],$upd_arr['keywords'])){
                DB::rollBack();
                $this->setError($goodsSpecService->error);
                return false;
            }
        }
        DB::commit();
        $this->setMessage('修改成功！');
        return true;
    }

    /**
     * 获取商品列表
     * @param $request
     * @return bool|mixed|null
     */
    public function getGoodsList($request){
        $page = $request['page'] ?? 1;
        $page_num = $request['page_num'] ?? 20;
        $keywords = $request['keywords'] ?? null;
        $category = $request['category'] ?? null;
        $status   = $request['status'] ?? null;
        $order      = 'id';
        $asc_desc   = 'desc';
        $where = ['deleted_at' => 0];
        $column = ['id','name','category','price','banner_ids','stock','is_recommend','status','created_at','updated_at'];
        if (!empty($category)){
            $where['category'] = $category;
        }
        if (!empty($status)){
            $where['status'] = $status;
        }
        if (!empty($keywords)){
            if (!$list = ShopGoodsRepository::search([$keywords => ['keywords']],$where,$column,$page,$page_num,$order,$asc_desc)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = ShopGoodsRepository::getList($where,$column,$order,$asc_desc,$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $list['data']   = ImagesService::getListImages($list['data'],['banner_ids' => 'single']);
        $category_ids   = array_column($list['data'],'category');
        $category_list  = ShopGoodsCategoryRepository::getAssignList($category_ids);
        foreach ($list['data'] as &$v){
            $v['category_title'] = '';
            if ($categories = $this->searchArray($category_list,'id',$v['category'])){
                $v['category_title'] = reset($categories)['name'];
            }
            $v['price']         = empty($v['price']) ? 0 : round($v['price'] / 100,2);
            $v['is_recommend']  = $v['is_recommend'] !== 0 ? 1 : 0;
            $v['status_title']        = ShopGoodsEnum::getStatus($v['status']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 获取商品详情
     * @param $id
     * @return bool|null
     */
    public function getGoodsDetail($id){
        if (!$goods = ShopGoodsRepository::getOne(['id' => $id])){
            $this->setError('商品信息不存在！');
            return false;
        }
        $category = ShopGoodsCategoryRepository::getOne(['id' => $goods['category']]);
        $goods['label_list']        = empty($goods['labels']) ? [] : explode(',',trim($goods['labels'],','));
        $goods['category_title']    = $category['name'] ?? '';
        $goods['category_icon']     = isset($category['icon_id']) ? CommonImagesRepository::getField(['id' => $category['icon_id']],'img_url') : '';
        $goods['price']             = empty($value['price']) ? 0.00 : round($value['price'] / 100,2);
        $goods['banner_list']       = CommonImagesRepository::getList(['id' => ['in',explode(',',$goods['banner_ids'])]],['id','img_url']);
        $goods['image_list']        = CommonImagesRepository::getList(['id' => ['in',explode(',',$goods['image_ids'])]],['id','img_url']);
        $goods['express_price']     = empty($value['express_price']) ? 0 : round($value['express_price'] / 100,2);
        $goods['is_recommend']      = $goods['is_recommend'] == 0 ? 2 : 1;
        $goods['status_title']      = ShopGoodsEnum::getStatus($goods['status']);
        $goods['spec_json']         = GoodsSpecRelateService::getGoodsSpecJson($id);
        unset($goods['deleted_at']);
        $this->setMessage('获取成功！');
        return $goods;
    }

    /**
     * 获取商城首页
     * @return mixed
     */
    public function getHome()
    {
        $result['banners']          = HomeBannersService::getHomeBanners(CommonHomeEnum::SHOPHOME);
        $result['announce']         = AnnounceService::getHomeAnnounce();
        $result['recommend_goods']  = $this->getRecommendGoodsList(null,16);
        $this->setMessage('获取成功！');
        return $result;
    }

    /**
     * H5获取推荐商品列表
     * @param array $where
     * @param int|null $count
     * @return array
     */
    public function getRecommendGoodsList($where = null,$count = null){
        if (is_null($where)){
            $where = ['is_recommend' => ['<>',0]];
        }
        $column = ['id','name','price','banner_ids','labels'];
        if (is_null($count)){
            if (!$list = ShopGoodsRepository::getList($where,$column,'is_recommend','desc')){
                $this->setMessage('暂无数据！');
                return [];
            }
        }else{
            if (!$goods_list = ShopGoodsRepository::getList($where,$column,'is_recommend','desc',1,16)){
                $this->setError('获取失败！');
                return [];
            }
            if (empty($goods_list['data'])){
                $this->setMessage('暂无数据！');
                return [];
            }
            $list = $goods_list['data'];
        }
        $list = ImagesService::getListImages($list,['banner_ids' => 'single']);
        foreach ($list as &$value){
            $value['price'] = '￥'.sprintf('%.2f',round($value['price'] / 100, 2));
            $value['labels']= empty($value['labels']) ? [] : explode(',',trim($value['labels'],','));
        }
        $this->setMessage('获取成功！');
        return $list;
    }


    /**
     * 获取商品详情
     * @param $request
     * @return array|bool|null
     */
    public function getGoodsDetailsById($request)
    {
        $column = ['id', 'name', 'price', 'banner_ids', 'labels',
            'stock', 'express_price', 'image_ids',
            'gift_score', 'score_deduction',
        ];
        if (!$goods_detail = ShopGoodsRepository::getOne(['id' => $request['id'], 'deleted_at' => 0], $column)) {
            $this->setError('商品不存在!');
            return false;
        }
        $goods_detail                   = ImagesService::getOneImagesConcise($goods_detail,['banner_ids' => 'several','image_ids' => 'several'],true);
        $goods_detail['sales']          = ShopOrderGoodsRepository::count(['goods_id' => $request['id']]);
        $goods_detail['labels']         = empty($goods_detail['labels']) ? [] : explode(',', trim($goods_detail['labels'], ','));
        $goods_detail['price']          = sprintf('%.2f', round($goods_detail['price'] / 100, 2));
        $goods_detail['express_price']  = sprintf('%.2f', round($goods_detail['express_price'] / 100, 2));
        $goods_detail['collect']        = MemberCollectRepository::exists(['id' => $request['id'],'deleted_at' => 0]) == false  ? '0' : '1';
        $goods_detail['comment']        = CommonCommentsRepository::getOneComment($goods_detail['id'],CommentsEnum::SHOP);
        $goods_detail['recommend']      = ShopGoodsRepository::getList(['id' => ['in',[2,3]]], ['id','name','banner_ids','labels','price']);
        foreach ($goods_detail['recommend'] as &$value){
            $value['price']     = '￥'.sprintf('%.2f',round($value['price'] / 100, 2));
            $value['labels']    = empty($value['labels']) ? [] : explode(',',trim($value['labels'],','));
        }
        $goods_detail['recommend'] = ImagesService::getListImagesConcise($goods_detail['recommend'],['banner_ids' => 'single'],true);
        $this->setMessage('获取成功!');
        return $goods_detail;
    }


    /**
     * 前端获取商品列表
     * @param $request
     * @return bool|mixed|null
     */
    public function goodsList($request){
        $page = $request['page'] ?? 1;
        $page_num = $request['page_num'] ?? 20;
        $keywords = $request['keywords'] ?? null;
        $category = $request['category'] ?? null;
        $order      = 'id';
        $asc_desc   = 'desc';
        $where = ['deleted_at' => 0,'status' => ShopGoodsEnum::PUTAWAY];
        if (!is_null($category)){
            $where['category'] = $category;
        }
        $column = ['id','name','price','banner_ids','labels'];
        if (!empty($keywords)){
            if (!$list = ShopGoodsRepository::search([$keywords => ['keywords']],$where,$column,$page,$page_num,$order,$asc_desc)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = ShopGoodsRepository::getList($where,$column,$order,$asc_desc,$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $list['data']   = ImagesService::getListImages($list['data'],['banner_ids' => 'single']);
        foreach ($list['data'] as &$value){
            $value['price'] = '￥'.sprintf('%.2f',round($value['price'] / 100, 2));
            $value['labels']= empty($value['labels']) ? [] : explode(',',trim($value['labels'],','));
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            