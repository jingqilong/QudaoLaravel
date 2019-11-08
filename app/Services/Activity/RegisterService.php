<?php
namespace App\Services\Activity;


use App\Enums\ActivityEnum;
use App\Enums\ActivityRegisterEnum;
use App\Repositories\ActivityDetailRepository;
use App\Repositories\ActivityRegisterRepository;
use App\Repositories\MemberGradeRepository;
use App\Repositories\MemberOrdersRepository;
use App\Repositories\MemberRepository;
use App\Services\BaseService;
use App\Services\Common\SmsService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            'member_id' => $member->m_id,
            'status' => ['<',5]])){
            $this->setError('您已经报过名了，请勿重复报名！');
            return false;
        }
        $add_arr = [
            'activity_id'   => $request['activity_id'],
            'member_id'     => $member->m_id,
            'name'          => $request['name'],
            'mobile'        => $request['mobile'],
            'activity_price'=> $activity['price'],
            'member_price'  => $member_price,
            'status'        => ActivityRegisterEnum::PENDING,
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (ActivityRegisterRepository::getAddId($add_arr)){
            //TODO 此处可以添加报名后发通知的事务
            #发送短信
            if (!empty($member->m_phone)){
                $sms = new SmsService();
                $content = '您好！欢迎参加活动《'.$activity['name'].'》,我们将在24小时内受理您的报名申请，如有疑问请联系客服：000-00000！';
                $sms->sendContent($member->m_phone,$content);
            }
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
        $members    = MemberRepository::getList(['m_id' => ['in',$member_ids]],['m_id','m_cname']);
        foreach ($list['data'] as &$value){
            $activity = $this->searchArray($activities,'id',$value['activity_id']);
            $member   = $this->searchArray($members,'m_id',$value['member_id']);
            $value['theme_name']    = reset($activity)['name'];
            $value['member_name']   = reset($member)['m_cname'];
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
        if ($member = MemberRepository::getOne(['m_id' => $register['member_id']])){
            $member_name = !empty($member['m_cname']) ? $member['m_cname'] : (!empty($member['m_ename']) ? $member['m_ename'] : (substr($member['m_phone'],-4)));
            $member_name = $member_name.$member['m_sex'];
            #短信通知
            if (!empty($member['m_phone'])){
                $smsService = new SmsService();
                $sms_template = [
                    ActivityRegisterEnum::SUBMIT        => '尊敬的'.$member_name.'您好！您报名的 '.$activity['name'].' 活动已经通过审核，活动开始时间：'.date('Y-m-d H:i',$activity['start_time']).',支付后即可参加活动！',
                    ActivityRegisterEnum::EVALUATION    => '尊敬的'.$member_name.'您好！您报名的 '.$activity['name'].' 活动已经通过审核，活动开始时间：'.date('Y-m-d H:i',$activity['start_time']).'，记得提前到场哦！',
                    ActivityRegisterEnum::NOPASS        => '尊敬的'.$member_name.'您好！您报名的 '.$activity['name'].' 活动审核未通过，请不要灰心，您还可以参加我们后续的活动哦！',
                ];
                $smsService->sendContent($member['m_phone'],$sms_template[$status]);
            }
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
        if (!$register = ActivityRegisterRepository::getOne(['sign_in_code' => $sign_in_code,'status' => ['>',ActivityRegisterEnum::EVALUATION]])){
            $this->setError('报名信息不存在！');
            return false;
        }
        if (!$activity = ActivityDetailRepository::getOne(['id' => $register['activity_id']])){
            $this->setError('活动信息不存在！');
            return false;
        }
        $time = time();
        if ($activity['start_time'] > ($time + 3600)){
            $this->setError('活动还没开始，不能签到！');
            return false;
        }
        if (($activity['end_time'] + 3600) < $time){
            $this->setError('活动还没开始，不能签到！');
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
        $member_list = MemberRepository::getList(['m_id' => $member_ids],['m_id','m_cname']);
        foreach ($list['data'] as &$value){
            $value['member_name'] = '';
            if ($member = $this->searchArray($member_list,'m_id',$value['member_id'])){
                $value['activity_name'] = reset($member)['m_cname'];
            }
            $value['sign_time'] = date('Y-m-d H:i',$value['is_register']);
            unset($value['is_register']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            