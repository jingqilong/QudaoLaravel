<?php

return [
    'start_node' => 1,
    'getway' =>[
//        'MEMBER_UPGRADE'         => '成员升级',
            1 => "\\App\\Services\\Member\\GradeOrdersService.NotFound",
//        'ACTIVITY_REGISTER'      => '活动报名',
            2 => "\\App\\Services\\Activity\\RegisterService.NotFound",
//        'PROJECT_DOCKING'        => '项目对接',
            3 => "\\App\\Services\\Project\\OrderService.NotFound",
//        'LOAN_RESERVATION'       => '贷款预约',
            4 => "\\App\\Services\\Loan\\PersonalService.getLoanInfo",
//        'ENTERPRISE_CONSULT'     => '企业咨询',
            5 => "\\App\\Services\\Enterprise\\OrderService.NotFound",
    ],
    'event_type' => [
        //use App\Events\SendDingTalkEmail;
        1 => 'DINGTALK_EMAIL',
        //use App\Events\SendWeChatPush;
        2 => 'SMS',
        //use App\Events\SendSiteMessage;
        3 => 'SITE_MESSAGE',
        //use App\Events\SendFlowSms;
        4 => 'WECHAT_PUSH',
    ],
    'process_starter' =>[  //获取发起人信息
//        'MEMBER_UPGRADE'         => '成员升级',
        1 => [ App\Services\Member\GradeOrdersService::class,'getCreatedUser'],
//        'ACTIVITY_REGISTER'      => '活动报名',
        2 => [ App\Services\Activity\RegisterService::class,'getCreatedUser'],
//        'PROJECT_DOCKING'        => '项目对接',
        3 => [ App\Services\Project\OrderService::class,'getCreatedUser'],
//        'LOAN_RESERVATION'       => '贷款预约',
        4 => [ App\Services\Loan\PersonalService::class,'getCreatedUser'],
//        'ENTERPRISE_CONSULT'     => '企业咨询',
        5 => [ App\Services\Enterprise\OrderService::class,'getCreatedUser'],
//        'HOUSR_RESERVATION'     => '看房预约',
        6 => [ App\Services\House\ReservationService::class,'getCreatedUser'],
//        'HOSPITAL_RESERVATION'     => '医疗预约',
        7 => [ App\Services\Medical\OrdersService::class,'getCreatedUser'],
//        'PRIME_RESERVATION'     => '精选生活预约',
        8 => [ App\Services\Prime\ReservationService::class,'getCreatedUser'],
    ],

];