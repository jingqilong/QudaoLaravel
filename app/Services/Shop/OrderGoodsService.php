<?php
namespace App\Services\Shop;


use App\Enums\CommentsEnum;
use App\Enums\ShopOrderEnum;
use App\Enums\ShopOrderTypeEnum;
use App\Repositories\CommonCommentsRepository;
use App\Repositories\CommonImagesRepository;
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
}
            