<?php
namespace App\Services\Shop;


use App\Enums\CommentsEnum;
use App\Enums\ShopOrderEnum;
use App\Enums\ShopOrderTypeEnum;
use App\Repositories\CommonCommentsRepository;
use App\Repositories\CommonImagesRepository;
use App\Repositories\ShopGoodsRepository;
use App\Repositories\ShopOrderGoodsRepository;
use App\Repositories\ShopOrderRelateRepository;
use App\Services\BaseService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class OrderGoodsService extends BaseService
{
    /**
     * 获取订单商品评论
     * @param $order_relate_id
     * @return array|bool
     */
    public function getOrderComments($order_relate_id){
        $member = Auth::guard('member_api')->user();
        if (!$order_relate = ShopOrderRelateRepository::getOne(['id' => $order_relate_id,'member_id' => $member->id,'deleted_at' => 0])){
            $this->setError('订单不存在！');
            return false;
        }
        if (!$order_goods_list = ShopOrderGoodsRepository::getAllList(['order_relate_id' => $order_relate_id])){
            $this->setError('该订单无商品信息！');
            return false;
        }
        if ($order_relate['order_type'] == ShopOrderTypeEnum::NEGOTIABLE){
            $goods_info_list = GoodsSpecRelateService::getNegotiableGoodsInfo($order_goods_list);
        }else{
            $goods_info_list = GoodsSpecRelateService::getListCommonInfo($order_goods_list);
        }
        $comment_column = ['id','content','related_id','image_ids','created_at'];
        $comment_where  = ['member_id' => $member->id,'type' => CommentsEnum::SHOP,'order_related_id' => $order_relate_id,'deleted_at' => 0];
        $comment_list   = CommonCommentsRepository::getAllList($comment_where,$comment_column) ?? [];
        CommonImagesRepository::bulkHasManyWalk(byRef($comment_list),['from' => 'image_ids','to' => 'id'],['*'],[],
            function ($src_item,$set_items){
                unset($src_item['image_ids']);
                $src_item['image_urls'] = Arr::pluck($set_items,'img_url');
                return $src_item;
            });
        $comment_list   = createArrayIndex($comment_list,'related_id');
        foreach ($goods_info_list as &$value){
            $value['is_comment']= ($order_relate['status'] == ShopOrderEnum::FINISHED) ? 1 : 0;//是否评论，1已评论（不能评论），0未评论（可以评论）
            $value['comment']   = [];
            if (isset($comment_list[$order_relate_id.','.$value['goods_id']])){
                $comment                = $comment_list[$order_relate_id.','.$value['goods_id']];
                $comment['created_at']  = date('Y.m.d / H:i',strtotime($comment['created_at']));
                $value['is_comment']    = 1;
                $value['comment']       = $comment;
            }
            unset($value['spec_relate_id'],$value['cart_id']);
        }
        $this->setMessage('获取成功！');
        return $goods_info_list;
    }

    /**
     * 获取评论公共信息
     * @param $relate_id
     * @return array
     */
    public function getCommentCommonInfo($relate_id){
        if (empty($relate_id)){
            return [];
        }
        list($order_relate_id,$goods_id) = explode(',',$relate_id);
        if (!$order_goods = ShopOrderGoodsRepository::getOne(['order_relate_id' => $order_relate_id,'goods_id' => $goods_id])){
            return [];
        }
        $goodsService = new GoodsService();
        $goods_info = $goodsService->getGoodsDetail($goods_id);
        if (!$goods_info){
            return [];
        }
        return [
            'relate_name'   => $goods_info['name'] . $order_goods['spec_relate_value'],
            'relate_images' => Arr::pluck($goods_info['banner_list'],'img_url'),
        ];
    }

    /**
     * 评论前检查
     * @param $related_id
     * @return bool
     */
    public function beforeCommentCheck($related_id){
        if (!preg_match('/^[\d+]+[,]+[\d+]*/',$related_id)){
            $this->setError('评论ID格式有误');
            return false;
        }
        list($order_relate_id,$goods_id) = explode(',',$related_id);
        if (!$order = ShopOrderRelateRepository::getOne(['id' => $order_relate_id])){
            $this->setError('订单信息不存在！');
            return false;
        }
        if ($order['status'] == ShopOrderEnum::FINISHED){
            $this->setError('该订单当前状态不能评价！');
            return false;
        }
        if ($order['status'] != ShopOrderEnum::RECEIVED){
            $this->setError('该订单还未签收，无法评论！');
            return false;
        }
        $this->setMessage('检查通过！');
        return true;
    }

    /**
     * 评论后回调
     * @param $relate_id
     * @return bool
     */
    public function commentCallback($relate_id){
        list($order_relate_id) = explode(',',$relate_id);
        if (!$order_goods_list = ShopOrderGoodsRepository::getAllList(['order_relate_id' => $order_relate_id])){
            $this->setError('订单商品不存在！');
            return false;
        }
        $relate_ids = [];
        foreach ($order_goods_list as $value){
            $relate_ids[] = $order_relate_id . ',' . $value['goods_id'];
        }
        $comment_count = CommonCommentsRepository::count(['relate_id' => ['in',$relate_ids]]);
        if ($comment_count < count($relate_ids)){
            $this->setMessage('订单商品未全部评论');
            return true;
        }
        if (!ShopOrderRelateRepository::getUpdId(['id' => $order_relate_id],['status' => ShopOrderEnum::FINISHED,'updated_at' => time()])){
            $this->setError('订单状态更新失败！');
            return false;
        }
        $this->setMessage('订单状态更新成功！');
        return true;
    }
}
            