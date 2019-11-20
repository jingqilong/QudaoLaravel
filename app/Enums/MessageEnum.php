<?php
/**
 * 消息枚举
 */
namespace App\Enums;


class MessageEnum extends BaseEnum
{
    public static $labels=[
        //消息类型状态
        'OPEN'          => '正常',
        'DISABLE'       => '禁用',
        //消息类型
        'SYSTEMNOTICE'  => '系统通知',
        'ANNOUNCE'      => '公告',
        'ACTIVITYENROLL'=> '活动报名通知',
        'ACTIVITYCHECK' => '活动签到通知',
        'HOUSEBOOKING'  => '房产预约通知',
        'MEDICALBOOKING'=> '医疗预约通知',
        'LOANBOOKING'   => '贷款预约通知',
        'PROJECTBOOKING'=> '项目对接预约通知',
        'PRIMEBOOKING'  => '精选生活预约通知',
        'SHOPOORDER'    => '商城订单通知',
        'SCOREBOOKING'  => '积分通知',
        //用户类型
        'MEMBER'        => '会员',
        'MERCHANT'      => '商户',
        'OAEMPLOYEES'   => 'OA员工'

    ];

    protected static $status = [
        0 => 'OPEN',
        1 => 'DISABLE'
    ];

    public static $category = [
        1   => 'SYSTEMNOTICE'  ,
        2   => 'ANNOUNCE'      ,
        3   => 'ACTIVITYENROLL',
        4   => 'ACTIVITYCHECK' ,
        5   => 'HOUSEBOOKING'  ,
        6   => 'MEDICALBOOKING',
        7   => 'LOANBOOKING'   ,
        8   => 'PROJECTBOOKING' ,
        9   => 'PRIMEBOOKING'  ,
        10  => 'SHOPOORDER'   ,
        11  => 'SCOREBOOKING'   ,
    ];

    public static $user_type = [
        1   => 'MEMBER',
        2   => 'MERCHANT',
        3   => 'OAEMPLOYEES',
    ];

    public static $template = [
        3 =>
            [
                'register'          => '您好！欢迎参加活动《activity_name》,我们将在24小时内受理您的报名申请，如有疑问请联系客服：021-53067999！',
                'auditPassEvaluation'=> '尊敬的member_name您好！您报名的activity_name活动已经通过审核，活动开始时间：time,支付后即可参加活动！',
                'auditPassSubmit'   => '尊敬的member_name您好！您报名的activity_name活动已经通过审核，活动开始时间：time，记得提前到场哦！',
                'auditNoPass'       => '尊敬的member_name您好！您报名的activity_name活动审核未通过，请不要灰心，您还可以参加我们后续的活动哦！',
            ],
        4 =>
            [
                'checkIn'           => '尊敬的member_name您好！您报名的activity_name活动已近开始了，快去签到吧！'
            ],
        5 =>
            [
                'auditPass'         => '尊敬的member_name您好！您的看房预约已经通过审核，看房时间：time，我们的负责人稍后会跟您联系，请耐心等待！',
                'auditNoPass'       => '尊敬的member_name.您的看房预约未通过审核，再看看其他房源吧！如有疑问请联系客服：021-53067999！'
            ],
        6 =>
            [
                'auditPass'         => '尊敬的member_name您好！您预约的《doctor_name》医生专诊,已通过审核,我们将在24小时内负责人联系您,请保持消息畅通，谢谢！',
                'auditNoPass'       => '尊敬的member_name您好！您预约的《doctor_name》医生专诊,未通过审核,请您联系客服021-53067999再次预约，谢谢！',
            ],
        7 =>
            [
                'auditPass'         => '尊敬的member_name您好！您的贷款预约已通过审核,我们将在24小时内负责人联系您,请保持消息畅通，谢谢！',
                'auditNoPass'       => '尊敬的member_name您好！您的贷款预约未通过审核,请您联系客服021-53067999再次预约，谢谢！',
            ],
        8 =>
            [
                'auditPass'         => '您好！您预约的《project_name》项目,已通过审核,我们将在24小时内负责人联系您，请保持消息畅通，谢谢！',
                'auditNoPass'       => '您好！您预约的《project_name》项目,未通过审核,请您联系客服021-53067999再次预约，谢谢！',
            ],
        9 =>
            [
                'auditPass'         => '尊敬的member_name您好！您的精选生活预约已经通过审核，预约时间：time！',
                'auditNoPass'       => '尊敬的member_name您的精选生活预约未通过审核，再看看其他服务吧！如有疑问请联系客服：021-53067999！',
            ],
        10 =>
            [
                'shipment'         => '尊敬的member_name您好！您的订单：order_no已发货,快递公司：express_company_name，快递单号：express_number，请注意查收！',
            ],
        11 =>
            [
                'increaseScore' => '尊敬的member_name,您于time在渠道PLUS资源共享平台explain score_name score分，当前可用remnant_score分。',
                'expenseScore'  => '尊敬的member_name,您于time在渠道PLUS资源共享平台消费score_name score分，当前可用remnant_score分。',
            ],
    ];

    // constants

    const OPEN          = 0;
    const DISABLE       = 1;

    //消息类型
    const SYSTEMNOTICE      = 1;
    const ANNOUNCE          = 2;
    const ACTIVITYENROLL    = 3;
    const ACTIVITYCHECK     = 4;
    const HOUSEBOOKING      = 5;
    const MEDICALBOOKING    = 6;
    const LOANBOOKING       = 7;
    const PROJECTBOOKING    = 8;
    const PRIMEBOOKING      = 9;
    const SHOPOORDER        = 10;
    const SCOREBOOKING      = 11;
    //用户类型
    const MEMBER            = 1;
    const MERCHANT          = 2;
    const OAEMPLOYEES       = 3;

    /**
     * @param int $value        值
     * @param string $default   默认值
     * @return mixed|string
     */
    public static function getCategoryStatus(int $value,$default = ''){
        return isset(self::$category[$value]) ? self::$labels[self::$category[$value]] : $default;
    }
    /**
     * @param int $value        值
     * @param string $default   默认值
     * @return mixed|string
     */
    public static function getCategory(int $value,$default = ''){
        return isset(self::$category[$value]) ? self::$labels[self::$category[$value]] : $default;
    }

    /**
     * 获取消息模板
     * @param integer $category 消息模块
     * @param string $action    结果
     * @param array $parameter  参数
     * @return mixed|string
     */
    public static function getTemplate ($category, $action, $parameter){
        if (!isset(self::$template[$category][$action])){
            return '';
        }
        $ket_arr = array_keys($parameter);
        $template = self::$template[$category][$action];
        $template = str_replace($ket_arr,$parameter,$template);
        return $template;
    }
}