<?php
namespace App\Services\Activity;


use App\Enums\ActivityEnum;
use App\Enums\ActivityRegisterEnum;
use App\Enums\MemberEnum;
use App\Enums\MessageEnum;
use App\Repositories\ActivityDetailRepository;
use App\Repositories\ActivityRegisterRepository;
use App\Repositories\ActivityThemeRepository;
use App\Repositories\CommonImagesRepository;
use App\Repositories\MemberGradeRepository;
use App\Repositories\MemberInfoRepository;
use App\Repositories\MemberOrdersRepository;
use App\Repositories\MemberBaseRepository;
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
use SimpleSoftwareIO\QrCode\Facades\QrCode;
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
        if (!$register_id = ActivityRegisterRepository::getAddId($add_arr)){
            $this->setError('报名失败！');
            return false;
        }
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
        $activities     = ActivityDetailRepository::getList(['id' => ['in',$activity_ids]],['id','name']);
        $members        = MemberBaseRepository::getList(['id' => ['in',$member_ids]],['id','ch_name']);
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
    public static function payCallBack($order_no, $status = ActivityRegisterEnum::EVALUATION){
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
        $member = $this->auth->user();
        $page = $request['page'] ?? 1;
        $page_num = $request['page_num'] ?? 20;
        $status = $request['status'] ?? null;
        $where  = ['member_id' => $member->id];
        if (!$register_list = ActivityRegisterRepository::getList($where,['id','activity_id','sign_in_code','status'])){
            $this->setMessage('暂无活动！');
            return [];
        }
        $activity_ids = array_column($register_list,'activity_id');
        $activity_column = ['id','name','area_code','address','price','start_time','end_time','cover_id','theme_id'];
        $activity_where = ['id' => ['in',$activity_ids]];
        switch ($status){
            case 1://未开始
                $activity_where['start_time'] = ['>',time()];
                break;
            case 2://进行中
                $activity_where['start_time'] = ['<',time()];
                $activity_where['end_time'] = ['>',time()];
                break;
            case 3://已结束
                $activity_where['end_time'] = ['<',time()];
                break;
            default:
                break;
        }
        if (!$activity_list = ActivityDetailRepository::getList($activity_where,$activity_column,'id','desc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $activity_list = $this->removePagingField($activity_list);
        if (empty($activity_list['data'])){
            $this->setMessage('暂无活动！');
            return $activity_list;
        }
        $theme_ids  = array_column($activity_list['data'],'theme_id');
        $themes     = ActivityThemeRepository::getList(['id' => ['in',$theme_ids]],['id','name','icon_id']);
        $icon_ids   = array_column($themes,'icon_id');
        $icons      = CommonImagesRepository::getList(['id' => ['in',$icon_ids]]);
        $activity_list['data'] = ImagesService::getListImagesConcise($activity_list['data'],['cover_id' => 'single']);
        foreach ($activity_list['data'] as &$value){
            $theme = $this->searchArray($themes,'id',$value['theme_id']);
            if ($theme)
                $icon  = $this->searchArray($icons,'id',reset($theme)['icon_id']);
            #处理地址
            list($area_address)     = $this->makeAddress($value['area_code'],'',3);
            $value['address']       = $area_address;
            $value['theme_name']    = $theme ? reset($theme)['name'] : '活动';
            $value['theme_icon']    = $icons ? reset($icon)['img_url'] : '';
            $value['price']         = empty($value['price']) ? '免费' : round($value['price'] / 100,2).'元';
            if ($value['start_time'] > time()){
                $value['status'] = '报名中';
            }
            if ($value['start_time'] < time() && $value['end_time'] > time()){
                $value['status'] = '进行中';
            }
            if ($value['end_time'] < time()){
                $value['status'] = '已结束';
            }
            $start_time             = date('Y年m/d',$value['start_time']);
            $end_time               = date('m/d',$value['end_time']);
            $value['activity_time'] = $start_time . '-' . $end_time;
            $sign_code_list         = $this->searchArray($register_list,'activity_id',$value['id']);
            $value['sign_in_code']  = reset($sign_code_list)['sign_in_code'];
            unset($value['theme_id'],$value['start_time'],$value['end_time'],$value['cover_id'],$value['area_code']);
        }
        $this->setMessage('获取成功！');
        return $activity_list;
    }

    /**
     * 生成入场券
     * @param $register_id
     * @return bool|void
     */
    public function getAdmissionTicket($register_id)
    {
        $member = $this->auth->user();
        if (!$register = ActivityRegisterRepository::getOne(['id' => $register_id])){
            $this->setError('报名信息不存在！');
            return false;
        }
        if (!in_array($register['status'],[ActivityRegisterEnum::EVALUATION,ActivityRegisterEnum::COMPLETED])){
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
        return $image_url;
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

    public function getActivityDetailOver($request)
    {
        $where  = ['id' => $request['id'],'deleted_at' => 0];
        $column = ['id','name','detail'];
        if (!$list = ActivityDetailRepository::getList($where,$column)){
            $this->setError('获取失败!');
            return false;
        }
    }

    /**
     * 获取活动分享二维码
     * @param $activity_id
     * @return array|bool
     */
    public function getShareQrCode($activity_id)
    {
        $url        = config('url.'.env('APP_ENV').'_url').'#'."/pages/activity/activitingDetail?listId=".$activity_id;
        $image_path = public_path('qrcode'.DIRECTORY_SEPARATOR.'activity-'.$activity_id.'.png');
        $res = [
            'url'           => $url,
            'qrcode_url'    => url('qrcode'.DIRECTORY_SEPARATOR.'activity-'.$activity_id.'.png')
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
}
            