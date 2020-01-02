<?php
namespace App\Services\Activity;


use App\Enums\ActivityEnum;
use App\Enums\ActivityRegisterAuditEnum;
use App\Enums\ActivityRegisterStatusEnum;
use App\Enums\CommonImagesEnum;
use App\Enums\CollectTypeEnum;
use App\Enums\MemberEnum;
use App\Enums\MessageEnum;
use App\Repositories\ActivityDetailRepository;
use App\Repositories\ActivityPastRepository;
use App\Repositories\ActivityPrizeRepository;
use App\Repositories\ActivityRegisterRepository;
use App\Repositories\ActivityRegisterViewRepository;
use App\Repositories\ActivityWinningRepository;
use App\Repositories\MemberCollectRepository;
use App\Repositories\MemberGradeRepository;
use App\Repositories\MemberInfoRepository;
use App\Repositories\MemberOrdersRepository;
use App\Repositories\MemberBaseRepository;
use App\Repositories\OaEmployeeRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
use App\Traits\HelpTrait;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Intervention\Image\Gd\Font;
use SimpleSoftwareIO\QrCode\BaconQrCodeGenerator;
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
     * @return mixed
     */
    public function register($request)
    {
        if (!$activity = ActivityDetailRepository::getOne(['id' => $request['activity_id']])){
            $this->setError('活动不存在！');
            return false;
        }
        $member         = $this->auth->user();
        $member_price   = $activity['price'];
        if ($activity['is_member'] == ActivityEnum::NOTALLOW){
            if (!$grade = MemberGradeRepository::getOne(['user_id' => $member->id,'grade' => ['in',[1,2,3,4,5,6,7,8,9]]])){
                $this->setError('本次活动仅限渠道PLUS成员参加，请升级为渠道PLUS成员！');
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
        if (ActivityRegisterRepository::exists(['activity_id' => $request['activity_id'], 'member_id' => $member->id, 'audit' => ['<>',ActivityRegisterAuditEnum::TURN_DOWN]])){
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
            'status'        => ActivityRegisterStatusEnum::SUBMIT,
            'audit'         => $activity['need_audit'] == ActivityEnum::NEEDAUDIT ? ActivityRegisterAuditEnum::PENDING_REVIEW : ActivityRegisterAuditEnum::PASS,
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if($activity['need_audit'] == ActivityEnum::NONEEDAUDIT && $member_price == 0){
            $add_arr['sign_in_code']  = ActivityRegisterRepository::getSignCode();
            //如果活动不需要审核并且活动为免费活动，设置报名状态为已支付（待评论）
            $add_arr['status'] = ActivityRegisterStatusEnum::EVALUATION;
        }
        DB::beginTransaction();
        if (!$register_id = ActivityRegisterRepository::getAddId($add_arr)){
            $this->setError('报名失败！');
            Loggy::write('error','用户：'.$member->mobile.' ，在活动《'.$activity['name'].'》报名时，添加报名信息失败，导致报名失败！报名信息：'.json_encode($add_arr));
            return false;
        }
        //如果是收费活动，创建订单
        $order_no = '';
        if ($member_price > 0){
            if (!$order_id = MemberOrdersRepository::addOrder($add_arr['member_price'],$add_arr['member_price'],$member->id,2)){
                $this->setError('报名失败！');
                Loggy::write('error','用户：'.$member->mobile.' ，在活动《'.$activity['name'].'》报名时，创建订单失败，导致报名失败！报名信息：'.json_encode($add_arr));
                DB::rollBack();
                return false;
            }
            if (!$order_no = MemberOrdersRepository::getField(['id' => $order_id],'order_no')){
                $this->setError('报名失败！');
                Loggy::write('error','用户：'.$member->mobile.' ，在活动《'.$activity['name'].'》报名时，获取订单号失败，导致报名失败！报名信息：'.json_encode($add_arr));
                DB::rollBack();
                return false;
            }
            if (!ActivityRegisterRepository::getUpdId(['id' => $register_id],['order_no' => $order_no])){
                $this->setError('报名失败！');
                Loggy::write('error','用户：'.$member->mobile.' ，在活动《'.$activity['name'].'》报名时，更新报名信息失败，导致报名失败！报名信息：'.json_encode($add_arr));
                DB::rollBack();
                return false;
            }
        }
        //如果需要审核，通知用户等待审核结果审核
        if ($activity['need_audit'] == ActivityEnum::NEEDAUDIT){
            $title   = '活动报名成功';
            $content = MessageEnum::getTemplate(MessageEnum::ACTIVITYENROLL,'register',['activity_name' => $activity['name']]);
            #发送短信
            if (!empty($member->m_phone)){
                $sms = new SmsService();
                $sms->sendContent($member->m_phone,$content);
            }
            #发送站内信
            SendService::sendMessage($member->id,MessageEnum::ACTIVITYENROLL,$title,$content,$register_id);
        }
        DB::commit();
        $this->setMessage('报名成功！');
        return [
            'register_id'       => $register_id,
            'register_status'   => $add_arr['status'],
            'order_no'          => $activity['need_audit'] == ActivityEnum::NEEDAUDIT ? '' : $order_no,
            'price'             => round($add_arr['member_price'] / 100,2)
        ];
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
        $audit          = $request['audit'] ?? '';
        $status_arr     = $request['status_arr'] ?? '';
        $activity_id    = $request['activity_id'] ?? '';
        $is_sign        = $request['is_sign'] ?? '';
        $page           = $request['page'] ?? 1;
        $page_num       = $request['page_num'] ?? 20;
        $where          = ['id' => ['>',0]];
        if (!empty($status)){
            $where['status'] = $status;
        }
        if (!empty($audit)){
            $where['audit'] = $audit;
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
        $audited_by     = array_column($list['data'],'audited_by');
        $activity_ids   = array_column($list['data'],'activity_id');
        $member_ids     = array_column($list['data'],'member_id');
        $activities     = ActivityDetailRepository::getList(['id' => ['in',$activity_ids]],['id','name']);
        $members        = MemberBaseRepository::getList(['id' => ['in',$member_ids]],['id','ch_name']);
        $audits         = OaEmployeeRepository::getList(['id' => ['in',$audited_by]],['id','real_name']);
        foreach ($list['data'] as &$value){
            $activity = $this->searchArray($activities,'id',$value['activity_id']);
            $member   = $this->searchArray($members,'id',$value['member_id']);
            $audit    = $this->searchArray($audits,'id',$value['audited_by']);
            $value['theme_name']    = reset($activity)['name'];
            $value['member_name']   = reset($member)['ch_name'];
            $value['activity_price']= empty($value['activity_price']) ? '免费' : round($value['activity_price'] / 100,2).' 元';
            $value['member_price']  = empty($value['member_price']) ? '免费' : round($value['member_price'] / 100,2).' 元';
            $value['status_title']  = ActivityRegisterStatusEnum::getStatus($value['status']);
            $value['audit_title']   = ActivityRegisterAuditEnum::getAudit($value['audit']);
            $value['audited_by']    = $audit ? reset($audit)['real_name'] : '';
            $value['audited_at']    = $value['audited_at'] != 0 ? date('Y-m-d H:m:i',$value['audited_at']) : '';
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
        $employees = Auth::guard('oa_api')->user();
        if (!$register = ActivityRegisterRepository::getOne(['id' => $register_id])){
            $this->setError('报名信息不存在！');
            return false;
        }
        if ($register['audit'] != ActivityRegisterAuditEnum::PENDING_REVIEW){
            $this->setError('报名申请已处理！');
            return false;
        }
        if (!$activity = ActivityDetailRepository::getOne(['id' => $register['activity_id']])){
            $this->setError('活动信息不存在！');
            return false;
        }
        DB::beginTransaction();
        $upd_register = [
            'audit'         => $audit,
            'sign_in_code'  => ActivityRegisterRepository::getSignCode(),
            'audited_by'    => $employees->id,
            'updated_at'    => time(),
            'audited_at'    => time(),
        ];
        if (!ActivityRegisterRepository::getUpdId(['id' => $register_id],$upd_register)){
            $this->setError('审核失败！');
            DB::rollBack();
            return false;
        }
        //通知用户
        if ($member = MemberBaseRepository::getOne(['id' => $register['member_id']])){
            $member_name = !empty($member['ch_name']) ? $member['ch_name'] : (!empty($member['en_name']) ? $member['en_name'] : (substr($member['mobile'],-4)));
            $member_name = $member_name.MemberEnum::getSex($member['sex']);
            if (ActivityRegisterAuditEnum::PASS == $audit){
                $sms_template = MessageEnum::getTemplate(MessageEnum::ACTIVITYENROLL, 'auditPassSubmit',
                    ['member_name' => $member_name,'activity_name' => $activity['name'],'time' => date('Y-m-d H:i',$activity['start_time'])]
                );
            }else{
                $sms_template = MessageEnum::getTemplate(MessageEnum::ACTIVITYENROLL, 'auditNoPass',
                    ['member_name' => $member_name,'activity_name' => $activity['name']]
                );
            }
            if (ActivityRegisterAuditEnum::PASS == $audit && $register['member_price'] == 0){
                $sms_template = MessageEnum::getTemplate(MessageEnum::ACTIVITYENROLL, 'auditPassEvaluation',
                        ['member_name' => $member_name,'activity_name' => $activity['name'],'time' => date('Y-m-d H:i',$activity['start_time'])]
                    );
            }
            #短信通知
            if (!empty($member['m_phone'])){
                $smsService = new SmsService();
                $smsService->sendContent($member['m_phone'],$sms_template);
            }
            $title   = '活动报名通知';
            #发送站内信
            SendService::sendMessage($register['member_id'],MessageEnum::ACTIVITYENROLL,$title,$sms_template,$register['activity_id']);
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
        if (!$register = ActivityRegisterRepository::getOne(['sign_in_code' => $sign_in_code,'audit' => ActivityRegisterAuditEnum::PASS,'status' => ['>',ActivityRegisterStatusEnum::SUBMIT]])){
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
        $where = ['is_register' => ['<>',0],'activity_id' => $request['activity_id']];
        if (!$list = ActivityRegisterRepository::getList($where,['id','member_id','is_register'],'is_register','asc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $member_ids = array_column($list['data'],'member_id');
        $member_list = MemberBaseRepository::getList(['id' => $member_ids],['id','ch_name']);
        foreach ($list['data'] as &$value){
            $value['member_name'] = '';
            if ($member = $this->searchArray($member_list,'id',$value['member_id'])){
                $value['member_name'] = reset($member)['ch_name'];
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
    public static function payCallBack($order_no, $status = ActivityRegisterStatusEnum::EVALUATION){
        if (!ActivityRegisterRepository::getUpdId(['order_no' => $order_no],['status' => $status])){
            Loggy::write('error','支付回调：活动订单状态更新失败！订单号：'.$order_no.'，支付结果：'.$status);
            Throw new \Exception('活动订单状态更新失败！');
        }
        return true;
    }

    /**
     * 获取我的活动列表
     * @param $request
     * @return array|bool|mixed|null
     */
    public function getMyActivityList($request){
        $member     = $this->auth->user();
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $status     = $request['status'] ?? null;
        $where      = ['member_id' => $member->id];
        switch ($status){
            case 1://未开始
                $where['start_time'] = ['>',time()];
                break;
            case 2://进行中
                $where['start_time'] = ['<',time()];
                $where['end_time'] = ['>',time()];
                break;
            case 3://已结束
                $where['end_time'] = ['<',time()];
                break;
            default:
                break;
        }
        $column = ['id','activity_id','name','area_code','address','price','start_time','end_time','cover_url','theme_name','theme_icon','register_status','register_audit','sign_in_code','order_no'];
        if (!$register_list = ActivityRegisterViewRepository::getList($where,$column,'id','desc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $register_list = $this->removePagingField($register_list);
        if (empty($register_list['data'])){
            $this->setMessage('暂无数据！');
            return $register_list;
        }
        foreach ($register_list['data'] as &$value){
            $value['register_id']   = $value['id'];
            $value['id']            = $value['activity_id'];
            #处理地址
            list($area_address)     = $this->makeAddress($value['area_code'],'',3);
            $value['address']       = $area_address;
            $value['price']         = empty($value['price']) ? '免费' : round($value['price'] / 100,2).'元';
            if ($value['start_time'] > time()){
                $value['status'] = 1;
                $value['status_title'] = '报名中';
            }
            if ($value['start_time'] < time() && $value['end_time'] > time()){
                $value['status'] = 2;
                $value['status_title'] = '进行中';
            }
            if (in_array($value['register_audit'],[ActivityRegisterAuditEnum::PENDING_REVIEW,ActivityRegisterAuditEnum::TURN_DOWN])){
                $value['status_title'] = ActivityRegisterAuditEnum::getAudit($value['register_audit']);
            }
            if ($value['register_audit'] == ActivityRegisterAuditEnum::PASS && $value['register_status'] == ActivityRegisterStatusEnum::SUBMIT){
                $value['status_title'] = ActivityRegisterStatusEnum::getStatus($value['register_status']);
            }
            if ($value['end_time'] < time()){
                $value['status'] = 3;
                $value['status_title'] = '已结束';
            }
            $start_time             = date('Y年m/d',$value['start_time']);
            $end_time               = date('m/d',$value['end_time']);
            $value['activity_time'] = $start_time . '-' . $end_time;
            unset($value['start_time'],$value['end_time'],$value['area_code'],$value['activity_id']);
        }
        $this->setMessage('获取成功！');
        return $register_list;
    }

    /**
     * 生成入场券
     * @param $register_id
     * @return mixed
     */
    public function getAdmissionTicket($register_id)
    {
        $member = $this->auth->user();
        if (!$register = ActivityRegisterRepository::getOne(['id' => $register_id])){
            $this->setError('报名信息不存在！');
            return false;
        }
        if ($register['audit'] != ActivityRegisterAuditEnum::PASS || $register['status'] == ActivityRegisterStatusEnum::SUBMIT){
            $this->setError('您的报名未成功，还不能获取入场券！');
            return false;
        }
        //获取用户信息及活动信息
        if (!$activity = ActivityDetailRepository::getOne(['id' => $register['activity_id']],['name','area_code','address','start_time','end_time'])){
            $this->setError('活动信息不存在！');
            return false;
        }
        //职称
        $member_employer = MemberInfoRepository::getField(['member_id' => $member->id],'employer');
        if (!empty($member_employer)){
            $member_employers = explode(',',$member_employer);
            $member_employer  = reset($member_employers);
        }
        $member_employer = empty($member_employer) ? '' : $member_employer;
        list($address)   = $this->makeAddress($activity['area_code'],$activity['address'],2);
        //生成图片
        $image_url = $this->createdAdmissionTicket([
            'activity_name'     => $activity['name'],
            'member_name'       => $member->ch_name,
            'member_title'      => $member_employer,
//            'member_title'      => '上海市茶叶行业协会 秘书长',
            'activity_time'     => date('Y年m/d',$activity['start_time']) .'-'. date('m/d',$activity['end_time']),
            'activity_address'  => $address,
            'sign_in_code'      => $register['sign_in_code']
        ]);
        $this->setMessage('生成成功！');
        $res = [
            'image_url'     => $image_url,
            'is_lottery'    => 0,
            'is_win'        => 0,
            'prize'         => []
        ];
        //检查是否已抽奖
        if ($winning = ActivityWinningRepository::getOne(['member_id' => $member->id,'activity_id' => $register['activity_id']])){
            $res['is_lottery'] = 1;
            if ($prize = ActivityPrizeRepository::getOne(['id' => $winning['prize_id']],['id','name','odds','image_ids','worth'])){
                $prize          = ImagesService::getOneImagesConcise($prize,['image_ids' => 'single'],true);
                $res['is_win']  = $prize['worth'] == 0 ? 0 : 1;
                $prize['name']  = '价值' . $prize['worth'] . '元的' . $prize['name'];
                unset($prize['odds'],$prize['id'],$prize['worth']);
                $res['prize']   = $res['is_win'] == 0 ? [] : $prize;
            }
        }
        //获取抽奖奖品
        return $res;
    }

    /**
     * 入场券图片合成
     * @param $data
     * @return UrlGenerator|string
     */
    public function createdAdmissionTicket($data){
        $img_path = public_path('admission_ticket'.DIRECTORY_SEPARATOR.$data['sign_in_code'].'.png');
        if (file_exists($img_path)){
            return url('admission_ticket'.DIRECTORY_SEPARATOR.$data['sign_in_code'].'.png');
        }
        //获取背景图
        $back_image = public_path('images'.DIRECTORY_SEPARATOR.'admission_ticket.png');
        $admission_image = Image::make($back_image);
        $font_path = public_path('font'.DIRECTORY_SEPARATOR.'pingfang'.DIRECTORY_SEPARATOR.'PingFangBold.ttf');
        //添加活动名称
        $activity_name = $data['activity_name'];
        $admission_image->text($activity_name,315,120,function (Font $font)use ($font_path){
            $font->file($font_path);
            $font->size(48);
            $font->color('#F5E6BA');
            $font->align('center');
            $font->valign('top');
        });
        //添加《渠道PLUS成员专享通道》
        $admission_image->line(117, 182, 177, 182, function ($draw) {
            $draw->color('#F5E6BA');
        });
        $admission_image->text("渠道PLUS成员专享通道",315,175,function (Font $font)use ($font_path){
            $font->file($font_path);
            $font->size(24);
            $font->color('#F5E6BA');
            $font->align('center');
            $font->valign('top');
        });
        $admission_image->line(453, 182, 513, 182, function ($draw) {
            $draw->color('#F5E6BA');
        });
        //添加会员名称
        $admission_image->text($data['member_name'],315,346,function (Font $font)use ($font_path){
            $font->file($font_path);
            $font->size(46);
            $font->color('#FFFFFF');
            $font->align('center');
            $font->valign('top');
        });
        //添加会员职务
        $admission_image->text($data['member_title'],315,400,function (Font $font)use ($font_path){
            $font->file($font_path);
            $font->size(38);
            $font->color('#FFFFFF');
            $font->align('center');
            $font->valign('top');
        });
        //添加活动时间
        $admission_image->text('TIME:'.$data['activity_time'],40,724,function (Font $font)use ($font_path){
            $font->file($font_path);
            $font->size(20);
            $font->color('#999999');
            $font->valign('left');
        });
        //添加活动地点
        $admission_image->text('ADD:'.$data['activity_address'],40,768,function (Font $font)use ($font_path){
            $font->file($font_path);
            $font->size(20);
            $font->color('#999999');
            $font->valign('left');
        });
        //生成二维码并插入到图片
        $qrcode_path = public_path(DIRECTORY_SEPARATOR.$data['sign_in_code'].'.png');
        $qr_code = new BaconQrCodeGenerator();
        $qr_code->format('png')
            ->size(120)
            ->margin(0)
            ->errorCorrection('M')
            ->generate($data['sign_in_code'], $qrcode_path);
        $admission_image->insert($qrcode_path, 'bottom-right', 40, 40);
        //使用完二维码后，删除它
        unlink($qrcode_path);
        $img_path = public_path('admission_ticket'.DIRECTORY_SEPARATOR.$data['sign_in_code'].'.png');
        $admission_image->save($img_path);
        return url('admission_ticket'.DIRECTORY_SEPARATOR.$data['sign_in_code'].'.png');
    }

    /**
     * 往期活动
     * @param $request
     * @return array|bool|null
     */
    public function getActivityPast($request)
    {
        $where  = ['activity_id' => $request['id'],'hidden' => 0];
        $column = ['id','resource_ids','top','presentation'];
        $auth = Auth::guard('member_api');
        $member = $auth->user();
        $res['is_collect'] = 0;
        if (MemberCollectRepository::exists(['type' => CollectTypeEnum::ACTIVITY,'target_id' => $request['id'],'member_id' => $member->id,'deleted_at' => 0])){
            $activity['is_collect'] = 1;
        }
        if (!ActivityPastRepository::exists(['activity_id' => $request['id']])) {
            $this->setError('没有此活动!');
            return false;
        }
        $res['banner']      = [];
        $res['video_list']  = [];
        $res['images_list'] = [];
        if (!$list = ActivityPastRepository::getList($where,$column,'top','desc')){
            $this->setError('获取失败');
            return false;
        }
        $list = ImagesService::getListImages($list,['resource_ids' => 'several'],false);
        foreach ($list as $value){
            if ($value['top'] == 1){
                $res['banner'][] = $value;
                continue;
            }
            foreach ($value['resource_urls'] as $item){
                if ($item['file_type'] == CommonImagesEnum::VIDEO){
                    $res['video_list'][] = $value;break;
                }
                if ($item['file_type'] == CommonImagesEnum::IMAGE){
                    $res['images_list'][] = $value;break;
                }
            }
            unset($value['resource_ids'],$value['top']);
        }
        $this->setMessage('获取成功!');
        return $res;
    }
    /**
     * 获取活动分享二维码
     * @param $activity_id
     * @return array|bool
     */
    public function getShareQrCode($activity_id)
    {
        $member = $this->auth->user;
        $url        = config('url.'.env('APP_ENV').'_url').'#'."/pages/activity/activitingDetail?listId=".$activity_id;
        $image_path = public_path('qrcode'.DIRECTORY_SEPARATOR.'activity-'.$activity_id.'.png');
        $res = [
            'url'           => $url,
            'qrcode_url'    => url('qrcode'.DIRECTORY_SEPARATOR.'activity-'.$activity_id.'.png'),
        ];
        if (file_exists($image_path)){
            $this->setMessage('获取成功！');
            return $res;
        }
        $qr_code = new BaconQrCodeGenerator();
        $qr_code->format('png')
            ->size(300)
            ->margin(1)
            ->errorCorrection('M')
            ->generate($url, $image_path);
        $this->setMessage('获取成功！');
        if (!file_exists($image_path)){
            $this->setError('生成失败！');
            return false;
        }
        return $res;
    }

    /**
     * oa 添加往期活动
     * @param $request
     * @return bool
     */
    public function addActivityPast($request)
    {
        //模板
        /*
         * resource_ids:[1,2,...]
         * top:'0' 0不置顶 1置顶
         * hidden:'0' 0隐藏 1显示
         * presentation:''
        */
        $activity_id = $request['activity_id'];
        if (!ActivityDetailRepository::getOne(['id' => $activity_id])){
            $this->setError('活动不存在!');
            return false;
        }
        $parameter   = json_decode($request['parameters'],true);
        $add_arr =[];
        foreach ($parameter as $value){
            if (!isset($value['resource_ids'])){
                $this->setError('资源图片(视频)不能为空!');
                return false;
            }
            if (!isset($value['presentation'])){
                $this->setError('文字描述不能为空！');
                return false;
            }
            $add_arr[] = [
                'activity_id'  => $activity_id,
                'top'          => $value['top'],
                'hidden'       => $value['hidden'],
                'resource_ids' => implode(',',$value['resource_ids']),
                'presentation' => $value['presentation'],
                'created_at'   => time(),
                'updated_at'   => time(),
            ];
        }
        DB::beginTransaction();
        ActivityPastRepository::delete(['activity_id' => $activity_id]);
        if (ActivityPastRepository::exists(['activity_id' => $request['activity_id']])){
            $this->setError('图文(视频)信息已存在!');
            DB::rollBack();
            return false;
        }
        if (!ActivityPastRepository::create($add_arr)){
            $this->setError('添加失败!');
            DB::rollBack();
            return false;
        }
        DB::commit();
        $this->setMessage('添加成功!');
        return true;
    }

    /**
     * 删除往期活动
     * @param $request
     * @return bool
     */
    public function delActivityPast($request)
    {
        if (!ActivityPastRepository::exists(['activity_id' => $request['activity_id']])){
            $this->setError('活动不存在!');
            return false;
        }
        if (!ActivityPastRepository::delete(['id' => $request['id']])){
            $this->setError('删除失败!');
            return false;
        }
        $this->setMessage('删除成功!');
        return true;
    }

    /**
     * OA 修改往期活动
     * @param $request
     * @return bool
     */
    public function editActivityPast($request)
    {
        $activity_id = $request['activity_id'];
        if (!ActivityPastRepository::exists(['activity_id' => $activity_id])){
            $this->setError('往期活动不存在!');
            return false;
        }
        $parameter   = json_decode($request['parameters'],true);
        $upd_arr = [];
        foreach ($parameter as $value){
            if (!isset($value['resource_ids'])){
                $this->setError('资源图片(视频)不能为空!');
                return false;
            }
            if (!isset($value['presentation'])){
                $this->setError('文字描述不能为空！');
                return false;
            }
            $upd_arr = [
                'activity_id'  => $activity_id,
                'top'          => $value['top'],
                'hidden'       => $value['hidden'],
                'resource_ids' => implode(',',$value['resource_ids']),
                'presentation' => $value['presentation'],
                'updated_at'   => time(),
            ];
        }
        DB::beginTransaction();
        ActivityPastRepository::delete(['activity_id' => $activity_id]);
        if (ActivityPastRepository::exists($upd_arr)){
            $this->setError('修改失败!');
            DB::rollBack();
            return false;
        }
        if (!ActivityPastRepository::create($upd_arr)){
            $this->setError('修改失败!');
            DB::rollBack();
            return false;
        }
        DB::commit();
        $this->setMessage('修改成功!');
        return true;
    }

    /**
     * OA 获取往期活动详情
     * @param $request
     * @return bool|mixed|null
     */
    public function getActivityPastList($request)
    {
        $where      = ['activity_id' => $request['activity_id']];
        $column     = ['resource_ids','presentation','hidden','top'];
        if (!$list = ActivityPastRepository::getList($where,$column)){
            $this->setMessage('获取成功');
            return json_encode($list);
        }
        $list = ImagesService::getListImages($list,['resource_ids' => 'several']);
        $this->setMessage('获取成功');
        foreach ($list as &$value){
            $value['resource_ids']  = explode(',',$value['resource_ids']);
        }
        return json_encode($list);
    }
    /**
     * 获取申请人ID
     * @param $register_id
     * @return mixed
     */
    public function getCreatedUser($register_id){
        return ActivityRegisterRepository::getField(['id',$register_id],'member_id');
    }

    /**
     * 返回流程中的业务列表
     * @param $register_ids
     * @return mixed
     */
    public function getProcessBusinessList($register_ids){
        if (empty($register_ids)){
            return [];
        }
        $column     = ['id','member_id','ch_name','mobile','name'];
        if (!$order_list = ActivityRegisterViewRepository::getAssignList($register_ids,$column)){
            return [];
        }
        $result_list = [];
        foreach ($order_list as $value){
            $result_list[] = [
                'id'            => $value['id'],
                'name'          => $value['name'],
                'member_id'     => $value['member_id'],
                'member_name'   => $value['ch_name'],
                'member_mobile' => $value['mobile'],
            ];
        }
        return $result_list;
    }
}
            