<?php
namespace App\Services\Activity;


use App\Enums\ActivityRegisterEnum;
use App\Repositories\ActivityDetailRepository;
use App\Repositories\ActivityPrizeRepository;
use App\Repositories\ActivityRegisterRepository;
use App\Repositories\ActivityWinningRepository;
use App\Repositories\CommonImagesRepository;
use App\Repositories\MemberBaseRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;

class PrizeService extends BaseService
{
    use HelpTrait;
    public $auth;

    /**
     * PrizeService constructor.
     * @param $auth
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }

    /**
     * 添加奖品
     * @param $request
     * @return bool
     */
    public function addPrize($request)
    {
        if (!ActivityDetailRepository::exists(['id' => $request['activity_id']])){
            $this->setError('活动不存在！');
            return false;
        }
        $add_arr = [
            'activity_id'   => $request['activity_id'],
            'name'          => $request['name'],
            'title'         => $request['title'],
            'number'        => $request['number'],
            'odds'          => $request['odds'],
            'image_ids'     => $request['image_ids'],
            'worth'         => $request['worth'],
            'link'          => $request['link'] ?? '',
        ];
        if (ActivityPrizeRepository::exists($add_arr)){
            $this->setError('该奖品已添加！');
            return false;
        }
        $add_arr['created_at'] = time();
        $add_arr['updated_at'] = time();
        if (!ActivityPrizeRepository::getAddId($add_arr)){
            $this->setError('添加失败！');
            return false;
        }
        $this->setMessage('添加成功！');
        return true;
    }

    /**
     * 删除奖品
     * @param $id
     * @return bool
     */
    public function deletePrize($id)
    {
        if (!$prize = ActivityPrizeRepository::getOne(['id' => $id])){
            $this->setError('奖品不存在！');
            return false;
        }
        if ($activity = ActivityWinningRepository::exists(['prize_id' => $id])){
            $this->setError('该奖品已被抽中，无法删除！');
            return false;
        }
        if (!ActivityPrizeRepository::delete(['id' => $id])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功！');
        return true;
    }

    /**
     * 修改奖品
     * @param $request
     * @return bool
     */
    public function editPrize($request)
    {
        if (!$prize = ActivityPrizeRepository::getOne(['id' => $request['id']])){
            $this->setError('奖品不存在！');
            return false;
        }
        if (!ActivityDetailRepository::exists(['id' => $request['activity_id']])){
            $this->setError('活动不存在！');
            return false;
        }
        $upd_arr = [
            'activity_id'   => $request['activity_id'],
            'name'          => $request['name'],
            'title'         => $request['title'],
            'number'        => $request['number'],
            'odds'          => $request['odds'],
            'image_ids'     => $request['image_ids'],
            'worth'         => $request['worth'],
            'link'          => $request['link'] ?? '',
        ];
        if (ActivityPrizeRepository::exists(array_merge($upd_arr,['id' => ['<>',$request['id']]]))){
            $this->setError('该奖品信息已存在！');
            return false;
        }
        $upd_arr['updated_at'] = time();
        if (!ActivityPrizeRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setError('修改失败！');
            return false;
        }
        $this->setMessage('修改成功！');
        return true;
    }

    /**
     * 获取奖品列表
     * @param $request
     * @return bool
     */
    public function getPrizeList($request)
    {
        $page       = $request['page'] ?? 1 ;
        $page_num   = $request['page_num'] ?? 20;
        $activity_id= $request['activity_id'] ?? 0;
        $where      = ['id' => ['>',0]];
        if (!empty($activity_id)){
            if (!ActivityDetailRepository::exists(['id' => $request['activity_id']])){
                $this->setError('活动不存在！');
                return false;
            }
            $where = ['activity_id' => $activity_id];
        }
        if (!$list = ActivityPrizeRepository::getList($where,['*'],'id','asc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $activity_ids = array_column($list['data'],'activity_id');
        $activities = ActivityDetailRepository::getList(['id' => ['in',$activity_ids]],['id','name']);
        foreach ($list['data'] as &$value){
            $value['images']     = [];
            $activity = $this->searchArray($activities,'id',$value['activity_id']);
            $value['activity_name'] = $activity ? reset($activity)['name'] : '活动已被删除';
            if (!empty($value['image_ids'])){
                $image_ids = explode(',',$value['image_ids']);
                if ($image_list = CommonImagesRepository::getList(['id' => ['in', $image_ids]],['id','img_url'])){
                    $value['images']= $image_list;
                }
            }
            $value['created_at']    = date('Y-m-d H:m:i',$value['created_at']);
            $value['updated_at']    = date('Y-m-d H:m:i',$value['updated_at']);
            unset($value['image_ids']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 活动抽奖
     * @param $activity_id
     * @return bool
     */
    public function raffle($activity_id)
    {
        if (!ActivityDetailRepository::exists(['id' => $activity_id])){
            $this->setError('活动不存在！');
            return false;
        }
        $member = $this->auth->user();
        if (!ActivityPrizeRepository::exists(['activity_id' => $activity_id])){
            $this->setError('该活动没有抽奖活动！');
            return false;
        }
        if (ActivityWinningRepository::exists(['member_id' => $member->id,'activity_id' => $activity_id])){
            $this->setError('您已经抽过奖了！');
            return false;
        }
        if (!$register = ActivityRegisterRepository::getOne(['activity_id' => $activity_id])){
            $this->setError('您还没有报名，不能参与抽奖！');
            return false;
        }
        if (!in_array($register['status'],[ActivityRegisterEnum::EVALUATION,ActivityRegisterEnum::COMPLETED])){
            $this->setError('您的报名还没有完成，不能参与抽奖！');
            return false;
        }
        $prize_all = ActivityPrizeRepository::getList(['activity_id' => $activity_id],['id','name','title','number','odds','image_ids','worth']);
        foreach ($prize_all as $key => &$prize){
            if ($prize['number'] !== 0 && ($prize['number'] <= ActivityWinningRepository::count(['prize_id' => $prize['id']]))){
                unset($prize_all[$key]);
            }
            unset($prize['number']);
        }
        if (empty($prize_all)){
            $this->setError('奖品已经被抽完了，下次再来吧！');
            return false;
        }
        foreach ($prize_all as $key => $value){
            $arr[$value['id']] = $value['odds'];
        }
        $rid = $this->get_rand($arr);
        $winning = $prize_all[$rid - 1];
        $add_winning = [
            'member_id'     => $member->id,
            'activity_id'   => $activity_id,
            'prize_id'      => $winning['id'],
            'created_at'    => time()
        ];
        $winning['images']     = [];
        if (!empty($winning['images_ids'])){
            $image_ids = explode(',',$winning['images_ids']);
            if ($image_list = CommonImagesRepository::getList(['id' => ['in', $image_ids]],['img_url'])){
                $image_list     = array_column($image_list,'img_url');
                $winning['images']= $image_list;
            }
        }
        unset($winning['odds'],$winning['images_ids']);
        if (!ActivityWinningRepository::getAddId($add_winning)){
            $this->setMessage('抽奖失败！');
            return $winning;
        }
        //TODO 此处添加抽奖成功后的事务
        $this->setMessage('恭喜你中奖啦！');
        return '恭喜您抽中了'.$winning['title'].',奖品将在活动现场发放给您！';
    }

    /**
     * 获取中奖纪录
     * @param $request
     * @return bool|mixed|null
     */
    public function getWinningList($request)
    {
        $page       = $request['page'] ?? 1 ;
        $page_num   = $request['page_num'] ?? 20;
        $activity_id= $request['activity_id'] ?? 0;
        $where      = ['id' => ['>',0]];
        if (!empty($activity_id)){
            if (!ActivityDetailRepository::exists(['id' => $request['activity_id']])){
                $this->setError('活动不存在！');
                return false;
            }
            $where = ['activity_id' => $activity_id];
        }
        if (!$list = ActivityWinningRepository::getList($where,['*'],'id','asc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $member_ids = array_column($list['data'],'member_id');
        $members = MemberBaseRepository::getList(['id' => ['in',$member_ids]],['id','ch_name']);
        $activity_ids = array_column($list['data'],'activity_id');
        $activities = ActivityDetailRepository::getList(['id' => ['in',$activity_ids]],['id','name']);
        $prize_ids = array_column($list['data'],'prize_id');
        $prizes = ActivityPrizeRepository::getList(['id' => ['in',$prize_ids]],['id','name','title','image_ids']);
        foreach ($list['data'] as &$value){
            $activity = $this->searchArray($activities,'id',$value['activity_id']);
            $value['activity_name'] = $activity ? reset($activity)['name'] : '活动已被删除';

            $member = $this->searchArray($members,'id',$value['member_id']);
            $value['member_name'] = $member ? reset($member)['ch_name'] : '未知';

            $prize = $this->searchArray($prizes,'id',$value['prize_id']);
            $value['prize_name'] = $prize ? reset($prize)['name'] : '未知';
            $value['prize_title'] = $prize ? reset($prize)['title'] : '未知';

            $value['is_get']        = $value['is_get'] == 1?'已领取':'未领取';
            $value['created_at']    = date('Y-m-d H:m:i',$value['created_at']);
            unset($value['member_id'],$value['activity_id'],$value['prize_id']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            