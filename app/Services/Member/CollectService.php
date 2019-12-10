<?php
namespace App\Services\Member;


use App\Enums\CommentsEnum;
use App\Enums\ShopOrderEnum;
use App\Repositories\CommonCommentsRepository;
use App\Repositories\PrimeMerchantRepository;
use App\Repositories\ShopGoodsRepository;
use App\Repositories\ShopOrderRelateRepository;
use App\Services\BaseService;
use App\Enums\CollectTypeEnum;
use App\Repositories\ActivityDetailRepository;
use App\Repositories\HouseDetailsRepository;
use App\Repositories\MemberCollectRepository;
use App\Services\Common\ImagesService;
use App\Services\Shop\GoodsSpecRelateService;
use App\Services\Shop\OrderRelateService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CollectService extends BaseService
{
    use HelpTrait;
    protected $auth;

    /**
     * MemberService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }

    /**
     * 检验收藏是否存在
     * @param $type
     * @param $target_id
     * @return bool
     */
    public function collectType($type, $target_id)
    {
        switch ($type){
            case CollectTypeEnum::ACTIVITY:
                if (!ActivityDetailRepository::exists(['id' => $target_id])){
                    $this->setError('活动不存在！');
                    return false;
                }
                break;
            case CollectTypeEnum::SHOP:
                if (!ShopGoodsRepository::exists(['id' => $target_id])){
                    $this->setError('商品不存在！');
                    return false;
                }
                break;
            case CollectTypeEnum::HOUSE:
                if (!HouseDetailsRepository::exists(['id' => $target_id])){
                    $this->setError('房源不存在！');
                    return false;
                }
                break;
            case CollectTypeEnum::PRIME:
                if (!PrimeMerchantRepository::exists(['id' => $target_id])){
                    $this->setError('商家不存在！');
                    return false;
                }
                break;
            default:
                $this->setError('暂无此收藏类别！');
                return false;
                break;
        }
        $this->setMessage('校验通过！');
        return true;
    }


    /**
     * 公共收藏
     * @param $type
     * @param $target_id
     * @return bool
     */
    public function isCollect($type, $target_id)
    {
        if (!$this->collectType($type, $target_id)){
            return false;
        }
        $member = $this->auth->user();
        $add_arr = [
            'type'          => $type,
            'target_id'     => $target_id,
            'member_id'     => $member->id,
        ];
        if ($id = MemberCollectRepository::getField(array_merge($add_arr,['deleted_at' => 0]),'id')){
            $add_arr['deleted_at'] = time();
            if (!MemberCollectRepository::getUpdId(['id' => $id],$add_arr)){
                $this->setError('取消失败！');
                return false;
            }
            $this->setMessage('取消成功！');
            return true;
        }
        if ($id = MemberCollectRepository::getField(array_merge($add_arr,['deleted_at' => ['>', 0]]),'id')){
            $add_arr['deleted_at'] = 0;
            if (!MemberCollectRepository::getUpdId(['id' => $id],$add_arr)){
                $this->setError('收藏失败！');
                return false;
            }
            $this->setMessage('收藏成功！');
            return true;
        }
        $add_arr['created_at'] = time();
        if (!MemberCollectRepository::getAddId($add_arr)){
            $this->setError('收藏失败！');
            return false;
        }
        $this->setMessage('收藏成功！');
        return true;
    }

    /**
     * 收藏列表
     * @param $request
     * @return array|bool|mixed|null
     */
    public function collectList($request)
    {
        if (empty(CollectTypeEnum::getType($request['type']))){
            $this->setError('暂无此收藏类别');
            return false;
        }
        $member     = $this->auth->user();
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $where = ['type' => $request['type'],'member_id' => $member->id,'deleted_at' => 0];
        if (!$collect_list = MemberCollectRepository::getList($where,['*'],'id','desc',$page,$page_num)){
            $this->setError('获取失败!');
            return false;
        }
        $collect_list = $this->removePagingField($collect_list);
        if (empty($collect_list['data'])){
            $this->setMessage('暂无数据');
            return $collect_list;
        }
        $collect_ids = array_column($collect_list['data'],'target_id');
        $request['collect_ids'] = $collect_ids;
        $request = [
            'collect_ids'   => $collect_ids,
            'page'          => $page,
            'page_num'      => $page_num,
            'type'          => $request['type'],
        ];
        switch ($request['type']){
            case CollectTypeEnum::ACTIVITY:
                $collect_list['data'] = ActivityDetailRepository::getCollectList($request);
                break;
            case CollectTypeEnum::SHOP:
                $collect_list['data'] = ShopGoodsRepository::getCollectList($request);
                break;
            case CollectTypeEnum::HOUSE:
                $collect_list['data'] = HouseDetailsRepository::getCollectList($request);
                break;
            case CollectTypeEnum::PRIME:
                $collect_list['data'] = PrimeMerchantRepository::getCollectList($request);
                break;
            default:
                $this->setError('暂无此收藏类别！');
                return false;
                break;
        }
        $this->setMessage('获取成功!');
        return $collect_list;
    }

    /**
     * OA 获取评论列表
     * @param $request
     * @return bool|mixed|null
     */
    public function commentsList($request)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $keywords   = $request['$keywords'] ?? null;
        $type       = $request['type'] ?? null;
        $where      = ['deleted_at' => 0];
        $column     = ['id','related_id','hidden','type','status','content','comment_avatar','comment_name','comment_name','image_ids','created_at'];
        if (!empty($type)){
            $where['type'] = $type;
        }
        if (!empty($keywords)){
            $keyword = [$keywords => ['comment_name']];
            if (!$comment_list = CommonCommentsRepository::search($keyword,$where,$column,$page,$page_num,'id','desc')){
                $this->setError('获取失败!');
                return false;
            }
        }
        if (!$comment_list = CommonCommentsRepository::getList($where,$column,'id','desc',$page,$page_num)){
            $this->setError('获取失败!');
            return false;
        }
        $comment_list = $this->removePagingField($comment_list);
        if (empty($comment_list['data'])) {
            $this->setMessage('没有评论!');
            return $comment_list;
        }
        $comment_list['data'] =  ImagesService::getListImagesConcise($comment_list['data'],['comment_avatar' => 'single']);
        $comment_list['data'] =  ImagesService::getListImagesConcise($comment_list['data'],['image_ids' => 'several']);
        $comment_list['data'] =  OrderRelateService::getCommentList($comment_list['data']);
        foreach ($comment_list['data'] as &$value){
            $value['type_name']     = CommentsEnum::getType($value['type']);
            $value['hidden_name']   = CommentsEnum::getHidden($value['hidden']);
            $value['status_name']   = CommentsEnum::getStatus($value['status']);
            unset($value['comment_avatar'],$value['related_id'],$value['image_ids']);
        }

        $this->setMessage('获取成功!');
        return $comment_list;
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
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $where      = ['related_id' => ['like','%,'.$request['id']],'type' => $request['type'],'hidden' => 0,'deleted_at' => 0];
        $column     = ['id','related_id','content','comment_avatar','comment_name','comment_name','image_ids','created_at'];
        if (!$comment_list = CommonCommentsRepository::getList($where,$column,'id','desc',$page,$page_num)){
            $this->setError('获取失败!');
            return false;
        }
        $comment_list = $this->removePagingField($comment_list);
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
                $order_relate_id = reset(explode(',',$related_id));
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
            'related_id'        => $request['related_id'],
            'image_ids'         => $request['image_ids'] ?? '',
            'status'            => CommentsEnum::SUBMIT,
            'hidden'            => CommentsEnum::ACTIVITE,
        ];
        if (CommonCommentsRepository::exists($add_arr)){
            $this->setError('评论已存在,请勿重复操作!');
            return false;
        }
        DB::beginTransaction();
        $add_arr['created_at']   = time();
        if (!$comments_id = CommonCommentsRepository::getAddId($add_arr)){
            $this->setError('评论添加失败!');
            DB::rollBack();
            return false;
        }
        //更改订单状态
        if (!ShopOrderRelateRepository::getUpdId(['id' => $order_relate_id],['status' => ShopOrderEnum::FINISHED])){
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
     * @param $request
     * @return bool
     */
    public function setCommentStatus($request)
    {
        if (!CommonCommentsRepository::getOne(['id' => $request['id']])){
            $this->setError('获取失败');
            return false;
        }
        $set_arr = [
            'status' => CommentsEnum::PASS,
            'hidden' => CommentsEnum::ACTIVITE,
        ];
        if (!CommonCommentsRepository::getUpdId(['id' => $request['id']],$set_arr)){
            $this->setError('设置状态失败!');
            return false;
        }
        $this->setMessage('设置成功!');
        return true;
    }
}

