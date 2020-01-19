<?php
namespace App\Services\Common;


use App\Enums\CommentsEnum;
use App\Enums\ProcessCategoryEnum;
use App\Enums\ShopGoodsEnum;
use App\Enums\ShopOrderEnum;
use App\Repositories\CommonCommentsRepository;
use App\Repositories\CommonCommentsViewRepository;
use App\Repositories\MemberBaseRepository;
use App\Repositories\ShopGoodSpecListViewRepository;
use App\Repositories\ShopGoodsRepository;
use App\Repositories\ShopGoodsSpecRelateRepository;
use App\Repositories\ShopOrderRelateRepository;
use App\Services\BaseService;
use App\Services\Shop\OrderRelateService;
use App\Traits\BusinessTrait;
use App\Traits\HelpTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommentsService extends BaseService
{
    use HelpTrait,BusinessTrait;
    protected $auth;

    /**
     * MemberService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }

    /**
     * 获取评论列表
     * @param $request
     * @return array|bool|mixed|null
     */
    public function commonList($request)
    {
        if (empty(CommentsEnum::getType($request['type']))){
            $this->setError('暂无此评论类别');
            return false;
        }
        $where      = ['related_id' => ['like','%,'.$request['id']],'type' => $request['type'],'hidden' => 0,'deleted_at' => 0];
        $column     = ['id','related_id','content','comment_avatar','comment_name','comment_name','image_ids','created_at'];
        if (!$comment_list = CommonCommentsRepository::getList($where,$column,'id','desc')){
            $this->setError('获取失败!');
            return false;
        }
        $comment_list = $this->removePagingField($comment_list);
        if (empty($comment_list)){
            $this->setMessage('获取成功!');
            return [];
        }
        $comment_list['data'] =  ImagesService::getListImagesConcise($comment_list['data'],['comment_avatar' => 'single']);
        $comment_list['data'] =  ImagesService::getListImagesConcise($comment_list['data'],['image_ids' => 'several']);
        $comment_list['data'] =  OrderRelateService::getCommentList($comment_list['data']);
        foreach ($comment_list['data'] as &$value){
            $value['created_at']    = date('Y-m-d',strtotime($value['created_at']));
            unset($value['comment_avatar'],$value['related_id'],$value['image_ids']);
        }
        $this->setMessage('获取成功!');
        return $comment_list;
    }

    /**
     * 添加评论
     * @param $request
     * @return bool|null
     */
    public function addComment($request)
    {
        $member    = $this->auth->user();
        $related_id = $request['related_id'];
        switch ($request['type']){
            case CommentsEnum::SHOP:
                if (!preg_match('/^[\d+]+[,]+[\d+]*/',$related_id)){
                    $this->setError('评论ID格式有误');
                    return false;
                }
                $related_arr = explode(',',$related_id);
                $order_relate_id = reset($related_arr);
                if (!$order = ShopOrderRelateRepository::getOne(['id' => $order_relate_id])){
                    $this->setError('订单信息不存在！');
                    return false;
                }
                if ($order['status'] == ShopOrderEnum::FINISHED){
                    $this->setError('该订单已评价！');
                    return false;
                }
                if ($order['status'] != ShopOrderEnum::RECEIVED){
                    $this->setError('该订单还未签收，无法评论！');
                    return false;
                }
                break;
            default:
                $this->setError('评论类型不存在！');
                return false;
        }
        $add_arr = [
            'member_id'         => $member->id,
            'content'           => $request['content'],
            'comment_name'      => $member->ch_name,
            'comment_avatar'    => $member->avatar_id,
            'type'              => CommentsEnum::SHOP,
            'order_related_id'  => $request['order_related_id'],
            'related_id'        => $request['related_id'],
            'image_ids'         => $request['image_ids'] ?? '',
            'status'            => CommentsEnum::SUBMIT,
            'hidden'            => CommentsEnum::HIDDEN,
        ];
        if (CommonCommentsRepository::exists($add_arr)){
            $this->setError('评论已存在,请勿重复操作!');
            return false;
        }
        DB::beginTransaction();
        $add_arr['created_at']   = time();
        $add_arr['updated_at']   = time();
        if (!$comments_id = CommonCommentsRepository::getAddId($add_arr)){
            $this->setError('评论添加失败!');
            DB::rollBack();
            return false;
        }
        //更改订单状态
        if (!ShopOrderRelateRepository::getUpdId(['id' => $request['related_id']],['status' => ShopOrderEnum::FINISHED])){
            $this->setError('评论添加失败!');
            DB::rollBack();
            return false;
        }
        $this->setMessage('评论添加成功!');
        DB::commit();
        return $comments_id;
    }

    /**
     * OA设置评论状态
     * @param $id
     * @param $status
     * @return bool
     */
    public function setCommentStatus($id,$status)
    {
        if (!CommonCommentsRepository::getOne(['id' => $id])){
            $this->setError('获取失败');
            return false;
        }
        $upd_arr = [
            'status' => $status,
            'hidden' => $status == CommentsEnum::PASS ? CommentsEnum::ACTIVITE : CommentsEnum::HIDDEN,
            'updated_at' => time()
        ];
        if (!CommonCommentsRepository::getUpdId(['id' => $id],$upd_arr)){
            $this->setError('设置状态失败!');
            return false;
        }
        $this->setMessage('设置成功!');
        return true;
    }

    /**
     * 获取评论详情
     * @param $request
     * @return array|bool|string|null
     */
    public function getCommentDetails($request)
    {
        switch ($request['type']){
            case CommentsEnum::SHOP:
                if (!$order = ShopOrderRelateRepository::getCommentDetails($request['id'])){
                    $this->setError('获取失败！');
                    return false;
                }
                break;
            default:
                $this->setError('评论类型不存在！');
                return false;
        }
        $this->setMessage('获取成功!');
    }


    /**
     * OA 获取评论列表
     * @param $request
     * @return bool|mixed|null
     */
    public function commentsList($request)
    {
        $employee   = Auth::guard('oa_api')->user();
        $keywords   = $request['keywords'] ?? null;
        $where      = ['deleted_at' => 0];
        $request_arr= Arr::only($request,['type','status','hidden']);
        $column     = ['id','related_id','hidden','mobile','type','image_ids','status','content','comment_avatar','comment_name','comment_avatar_url','created_at','deleted_at'];
        foreach ($request_arr as $key => $value){
            if (!is_null($value)) $where[$key] = $value;
        }
        if (!is_null($keywords)){
            $keyword = [$keywords => ['comment_name','mobile']];
            if (!$comment_list = CommonCommentsViewRepository::search($keyword,$where,$column,'id','desc')){
                $this->setError('获取失败!');
                return false;
            }
        }else{
            if (!$comment_list = CommonCommentsViewRepository::getList($where,$column,'id','desc')){
                $this->setError('获取失败!');
                return false;
            }
        }
        $comment_list = $this->removePagingField($comment_list);
        if (empty($comment_list['data'])) {
            $this->setMessage('没有评论!');
            return $comment_list;
        }
        $comment_list['data'] = ImagesService::getListImagesConcise($comment_list['data'],['image_ids' => 'several']);
        foreach ($comment_list['data'] as &$value){
            $related_arr = explode(',',$value['related_id']);
            $value['order_relate_id'] = end($related_arr);
            $value['type_name']     = CommentsEnum::getType($value['type']);
            $value['hidden_name']   = CommentsEnum::getHidden($value['hidden']);
            $value['status_name']   = CommentsEnum::getStatus($value['status']);
            #获取流程信息
            $value['progress']      = $this->getBusinessProgress($value['id'],ProcessCategoryEnum::COMMON_COMMENTS,$employee->id);
            unset($value['comment_avatar'],$value['related_id'],$value['image_ids']);
        }
        switch ($request_arr['type']){
            case CommentsEnum::SHOP:
                $comment_list['data'] = $this->getShopCommonService($comment_list['data']);
                break;
                default;
        }
        $this->setMessage('获取成功!');
        return $comment_list;
    }

    /**
     * 评论显示开关
     * @param $id
     * @param $hidden
     * @return bool
     */
    public function setCommentHidden($id, $hidden)
    {
        if (!$comments_info = CommonCommentsRepository::getOne(['id' => $id])){
            $this->setError('获取失败');
            return false;
        }
        if ($hidden == CommentsEnum::ACTIVITE){
            $upd_arr = [
                'status' => CommentsEnum::PASS,
                'hidden' => $hidden,
                'updated_at' => time()
            ];
        }else{
            $upd_arr = [
                'hidden' => $hidden,
                'updated_at' => time()
            ];
        }
        if (!CommonCommentsRepository::getUpdId(['id' => $id],$upd_arr)){
            $this->setError('设置失败!');
            return false;
        }
        $this->setMessage('设置成功!');
        return true;
    }
    /**
     * 获取申请人ID
     * @param $id
     * @return mixed
     */
    public function getCreatedUser($id){
        return CommonCommentsRepository::getField(['id' => $id],'member_id');
    }

    /**
     * 返回流程中的业务列表
     * @param $comment_ids
     * @return array
     */
    public function getProcessBusinessList($comment_ids)
    {
        if (empty($comment_ids)) {
            return [];
        }
        $column = ['id', 'member_id', 'type'];
        if (!$order_list = CommonCommentsRepository::getAssignList($comment_ids, $column)) {
            return [];
        }
        $member_ids = array_column($order_list, 'member_id');
        $member_list = MemberBaseRepository::getAssignList($member_ids, ['id', 'ch_name', 'mobile']);
        $member_list = createArrayIndex($member_list, 'id');
        $result_list = [];
        foreach ($order_list as $value) {
            $member = $member_list[$value['member_id']] ?? [];
            $result_list[] = [
                'id' => $value['id'],
                'name' => CommentsEnum::getType($value['type']) . '评论审核',
                'member_id' => $value['member_id'],
                'member_name' => $member['ch_name'] ?? '',
                'member_mobile' => $member['mobile'] ?? '',
            ];
        }
        return $result_list;
    }

    /**
     * 获取商城的service
     * @param array $data
     * @return array|bool
     */
    private function getShopCommonService(array $data)
    {
        $data = ShopGoodsRepository::bulkHasOneWalk(
            $data,
            ['from' => 'order_relate_id','to' => 'id'],
            ['id','name'],
            [],
            function ($src_item,$shop_items){
                $src_item['goods_service'] = $shop_items['name'] ?? '';
                return $src_item;
            }
        );
        return $data;
    }

    /**
     * OA查看评论详情
     * @param $request
     * @return array|bool
     */
    public function commentDetails($request)
    {
        $employee = Auth::guard('oa_api')->user();
        switch ($request['type']){
            case CommentsEnum::SHOP:
                if (!$order = $this->getShopCommentDetails($request['id'])){
                    $this->setError('获取失败！');
                    return false;
                }
                break;
            default:
                $this->setError('评论类型不存在！');
                return false;
        }
        $this->setMessage('获取成功!');
        return $this->getBusinessDetailsProcess($order,ProcessCategoryEnum::SHOP_NEGOTIABLE_ORDER,$employee->id);
    }


    /**
     * 获取评论商品的详情
     * @param $id
     * @return array|bool|null
     */
    protected function getShopCommentDetails($id)
    {
        $column     = ['id','related_id','hidden','mobile','type','image_ids','status','content','comment_avatar','comment_name','comment_avatar_url','created_at'];
        if(!$comments_info = CommonCommentsViewRepository::getOne(['id' => $id],$column)){
            $this->setError('获取失败!');
            return false;
        }
        $related_arr = explode(',',$comments_info['related_id']);
        $order_relate_id = end($related_arr);
        $shop_spec      = ShopGoodsSpecRelateRepository::getField(['goods_id' => $order_relate_id],'spec_ids');
        $shop_good_spec = ShopGoodSpecListViewRepository::getOne(['goods_id' => $order_relate_id,'spec_ids' => $shop_spec]);
        $shop_info      = ShopGoodsRepository::getOne(['id' => $order_relate_id],['name','banner_ids','negotiable']);
        $comments_info['spec_value'] = '';
        if (!is_null($shop_good_spec)){
            $comments_info['spec_value'] = $shop_good_spec['spec_name'] . ':' . $shop_good_spec['spec_value'];
            $comments_info['price'] = empty($shop_good_spec['price']) ? 0 : round($shop_good_spec['price'] / 100,2);
        }
        $comments_info['price'] = $shop_info['negotiable'] == ShopGoodsEnum::NEGOTIABLE ? ShopGoodsEnum::getNegotiable($shop_info['negotiable']) : $comments_info['price'];
        $comments_info = ImagesService::getOneImagesConcise($comments_info,['image_ids' => 'several']);
        $shop_info     = ImagesService::getOneImagesConcise($shop_info,['banner_ids' => 'single']);
        $comments = array_merge($comments_info,$shop_info);
        $comments['name']        = $comments['name'] .'|商品'. $comments['spec_value'];
        $comments['type_name']   = CommentsEnum::getType($comments['type']);
        $comments['hidden_name'] = CommentsEnum::getHidden($comments['hidden']);
        $comments['status_name'] = CommentsEnum::getStatus($comments['status']);
        unset($comments['negotiable'],$comments['banner_ids'],$shop_info['negotiable'],$comments['comment_avatar'],$comments['related_id'],$comments['spec_value'],$comments['image_ids']);
        return $comments;
    }
}
            