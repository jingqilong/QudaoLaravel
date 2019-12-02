<?php
namespace App\Services\Activity;


use App\Enums\ActivityEnum;
use App\Enums\ActivityRegisterEnum;
use App\Enums\MemberEnum;
use App\Enums\MessageEnum;
use App\Repositories\ActivityDetailRepository;
use App\Repositories\ActivityRegisterRepository;
use App\Repositories\MemberGradeRepository;
use App\Repositories\MemberOrdersRepository;
use App\Repositories\MemberBaseRepository;
use App\Services\BaseService;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tolawho\Loggy\Facades\Loggy;

class RegisterService extends BaseService
{
    use HelpTrait;
    public $auth;

    /**
     * CollectService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }

    /**
     * 活动报名
     * @param $request
     * @return bool
     */
    public function register($request)
    {
        if (!$activity = ActivityDetailRepository::getOne(['id' => $request['activity_id']])){
            $this->setError('活动不存在！');
            return false;
        }
        $member = $this->auth->user();
        $member_price = $activity['price'];
        if ($activity['is_member'] == ActivityEnum::NOTALLOW){
            if (!$grade = MemberGradeRepository::getOne(['user_id' => $member])){
                $this->setError('本次活动仅限会员参加！');
                return false;
            }
            //计算会员价格
            $member_price   = $this->discount($grade['grade'],$activity['price']);
        }
        $time = time();
        if ($time > $activity['start_time'] && $time < $activity['end_time']){
            $this->setError('活动已经开始，无法进行报名了！');
            return false;
        }
        if ($activity['end_time'] < $time){
            $this->setError('活动已经结束了，下次再来吧！');
            return false;
        }
        if (ActivityRegisterRepository::exists([
            'activity_id' => $request['activity_id'],
            'member_id' => $member->id,
            'status' => ['<',5]])){
            $this->setError('您已经报过名了，请勿重复报名！');
            return false;
        }
        $add_arr = [
            'activity_id'   => $request['activity_id'],
            'member_id'     => $member->id,
            'name'          => $request['name'],
            'mobile'        => $request['mobile'],
            'activity_price'=> $activity['price'],
            'member_price'  => $member_price,
            'status'        => ActivityRegisterEnum::PENDING,
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if ($register_id = ActivityRegisterRepository::getAddId($add_arr)){
            $title   = '活动报名成功';
            $content = MessageEnum::getTemplate(MessageEnum::ACTIVITYENROLL,'register',['activity_name' => $activity['name']]);
            #发送短信
            if (!empty($member->m_phone)){
                $sms = new SmsService();
                $sms->sendContent($member->m_phone,$content);
            }
            #发送站内信
            SendService::sendMessage($member->id,MessageEnum::ACTIVITYENROLL,$title,$content,$register_id);
            $this->setMessage('报名成功！');
            return true;
        }
        $this->setError('报名失败！');
        return false;
    }


    /**
     * 获取报名列表
     * @param $request
     * @return bool|null
     */
    public function getRegisterList($request)
    {
        $keywords       = $request['keywords'] ?? '';
        $status         = $request['status'] ?? '';
        $status_arr     = $request['status_arr'] ?? '';
        $activity_id    = $request['activity_id'] ?? '';
        $is_sign        = $request['is_sign'] ?? '';
        $page           = $request['page'] ?? 1;
        $page_num       = $request['page_num'] ?? 20;
        $where          = ['id' => ['>',0]];
        if (!empty($status)){
            $where['status'] = $status;
        }
        if (!empty($status_arr)){
            $where['status'] = ['in',$status_arr];
        }
        if (!empty($activity_id)){
            $where['activity_id'] = $activity_id;
        }
        if (!empty($is_sign)){
            if ($is_sign == 1){
                $where['is_register'] = ['>',0];
            }else{
                $where['is_register'] = 0;
            }
        }
        if (!empty($keywords)){
            $list = ActivityRegisterRepository::search([$keywords => ['name','mobile','sign_in_code']],$where,['*'],$page,$page_num,'id','desc');
        }else{
            $list = ActivityRegisterRepository::getList($where,['*'],'id','desc',$page,$page_num);
        }
        if (!$list){
            $this->setError('获取失败！');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $activity_ids   = array_column($list['data'],'activity_id');
        $member_ids     = array_column($list['data'],'member_id');
        $activities = ActivityDetailRepository::getList(['id' => ['in',$activity_ids]],['id','name']);
        $members    = MemberBaseRepository::getList(['id' => ['in',$member_ids]],['id','ch_name']);
        foreach ($list['data'] as &$value){
            $activity = $this->searchArray($activities,'id',$value['activity_id']);
            $member   = $this->searchArray($members,'id',$value['member_id']);
            $value['theme_name']    = reset($activity)['name'];
            $value['member_name']   = reset($member)['ch_name'];
            $value['activity_price']= empty($value['activity_price']) ? '免费' : round($value['activity_price'] / 100,2).' 元';
            $value['member_price']  = empty($value['member_price']) ? '免费' : round($value['member_price'] / 100,2).' 元';
            $value['status_title']  = ActivityRegisterEnum::getStatus($value['status']);
            $value['created_at']    = date('Y-m-d H:m:i',$value['created_at']);
            $value['updated_at']    = date('Y-m-d H:m:i',$value['updated_at']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 活动报名审核
     * @param $register_id
     * @param $audit
     * @return bool
     */
    public function auditRegister($register_id, $audit)
    {
        if (!$register = ActivityRegisterRepository::getOne(['id' => $register_id])){
            $this->setError('报名信息不存在！');
            return false;
        }
        if ($register['status'] > ActivityRegisterEnum::PENDING){
            $this->setError('报名申请已处理！');
            return false;
        }
        if (!$activity = ActivityDetailRepository::getOne(['id' => $register['activity_id']])){
            $this->setError('活动信息不存在！');
            return false;
        }
        DB::beginTransaction();
        $status = ActivityRegisterEnum::NOPASS;
        if ($audit == 1){
            $status = ActivityRegisterEnum::SUBMIT;
            if ($register['member_price'] == 0){
                $status = ActivityRegisterEnum::EVALUATION;
            }
        }
        $upd_register = [
            'status'        => $status,
            'sign_in_code'  => ActivityRegisterRepository::getSignCode(),
            'updated_at'    => time()
        ];
        if (!ActivityRegisterRepository::getUpdId(['id' => $register_id],$upd_register)){
            $this->setError('审核失败！');
            DB::rollBack();
            return false;
        }
        //创建订单
        if ($register['member_price'] > 0 && $status == ActivityRegisterEnum::SUBMIT){
            if (!$order_id = MemberOrdersRepository::addOrder($register['member_price'],$register['member_price'],$register['member_id'],2)){
                $this->setError('审核失败！');
                DB::rollBack();
                return false;
            }
            if (!$order = MemberOrdersRepository::getOne(['id' => $order_id])){
                $this->setError('审核失败！');
                DB::rollBack();
                return false;
            }
            if (!ActivityRegisterRepository::getUpdId(['id' => $register_id],['order_no' => $order['order_no']])){
                $this->setError('审核失败！');
                DB::rollBack();
                return false;
            }
        }
        //通知用户
        if ($member = MemberBaseRepository::getOne(['id' => $register['member_id']])){
            $member_name = !empty($member['ch_name']) ? $member['ch_name'] : (!empty($member['en_name']) ? $member['en_name'] : (substr($member['mobile'],-4)));
            $member_name = $member_name.MemberEnum::getSex($member['sex']);
            $sms_template = [
                ActivityRegisterEnum::SUBMIT        =>
                    MessageEnum::getTemplate(
                        MessageEnum::ACTIVITYENROLL,
                        'auditPassSubmit',
                        ['member_name' => $member_name,'activity_name' => $activity['name'],'time' => date('Y-m-d H:i',$activity['start_time'])]
                    ),
                ActivityRegisterEnum::EVALUATION    =>
                    MessageEnum::getTemplate(
                        MessageEnum::ACTIVITYENROLL,
                        'auditPassEvaluation',
                        ['member_name' => $member_name,'activity_name' => $activity['name'],'time' => date('Y-m-d H:i',$activity['start_time'])]
                    ),
                ActivityRegisterEnum::NOPASS        =>
                    MessageEnum::getTemplate(
                        MessageEnum::ACTIVITYENROLL,
                        'auditNoPass',
                        ['member_name' => $member_name,'activity_name' => $activity['name']]
                    ),
            ];
            #短信通知
            if (!empty($member['m_phone'])){
                $smsService = new SmsService();
                $smsService->sendContent($member['m_phone'],$sms_template[$status]);
            }
            $title   = '活动报名通知';
            #发送站内信
            SendService::sendMessage($register['member_id'],MessageEnum::ACTIVITYENROLL,$title,$sms_template[$status],$register_id);
        }
        $this->setMessage('审核成功！');
        DB::commit();
        return true;
    }

    /**
     * 活动签到
     * @param $sign_in_code
     * @return bool
     */
    public function sign($sign_in_code)
    {
        if (!$register = ActivityRegisterRepository::getOne(['sign_in_code' => $sign_in_code,'status' => ['>',ActivityRegisterEnum::SUBMIT]])){
            $this->setError('报名信息不存在！');
            return false;
        }
        if (!$activity = ActivityDetailRepository::getOne(['id' => $register['activity_id']])){
            $this->setError('活动信息不存在！');
            return false;
        }
        $time = time();
        if ($activity['start_time'] > ($time + $activity['signin'] * 60)){
            $this->setError('活动还没开始，不能签到！');
            return false;
        }
        if (($activity['end_time'] + 3600) < $time){
            $this->setError('活动已经结束，不能签到了！');
            return false;
        }
        if (!ActivityRegisterRepository::getUpdId(['sign_in_code' => $sign_in_code],['is_register' => $time,'updated_at' => $time])){
            $this->setError('签到失败！');
            return false;
        }
        $this->setMessage('签到成功！');
        return true;
    }

    /**
     * 签到列表
     * @param $request
     * @return bool|null
     */
    public function signList($request)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page'] ?? 20;
        $where = ['status' => ['>',ActivityRegisterEnum::EVALUATION],'is_register' => ['>',0]];
        if (!$list = ActivityRegisterRepository::getList($where,['id','member_id','is_register'],'is_register','asc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        unset($list['first_page_url'], $list['from'],
            $list['from'], $list['last_page_url'],
            $list['next_page_url'], $list['path'],
            $list['prev_page_url'], $list['to']);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $member_ids = array_column($list['data'],'member_id');
        $member_list = MemberBaseRepository::getList(['id' => $member_ids],['id','ch_name']);
        foreach ($list['data'] as &$value){
            $value['member_name'] = '';
            if ($member = $this->searchArray($member_list,'id',$value['member_id'])){
                $value['activity_name'] = reset($member)['ch_name'];
            }
            $value['sign_time'] = date('Y-m-d H:i',$value['is_register']);
            unset($value['is_register']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 支付回调
     * @param $order_no
     * @param int $status
     * @return bool
     * @throws \Exception
     */
    public static function payCallBack($order_no, $status = ActivityRegisterEnum::EVALUATION){
        if (!ActivityRegisterRepository::getUpdId(['order_no' => $order_no],['status' => $status])){
            Loggy::write('error','支付回调：活动订单状态更新失败！订单号：'.$order_no.'，支付结果：'.$status);
            Throw new \Exception('活动订单状态更新失败！');
        }
        return true;
    }
}
            