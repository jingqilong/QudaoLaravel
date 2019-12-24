<?php

return [
    'getway' =>[
//        'MEMBER_REGISTER'        => '成员注册',
            1 => "\\App\\Services\\Member\\GradeOrdersService.NotFound",
//        'MEMBER_UPGRADE'         => '成员升级',
            2 => "\\App\\Services\\Member\\GradeOrdersService.NotFound",
//        'ACTIVITY_REGISTER'      => '活动报名',
            3 => "\\App\\Services\\Activity\\RegisterService.NotFound",
//        'PROJECT_DOCKING'        => '项目对接',
            4 => "\\App\\Services\\Project\\OrderService.NotFound",
//        'LOAN_RESERVATION'       => '贷款预约',
            5 => "\\App\\Services\\Loan\\PersonalService.getLoanInfo",
//        'ENTERPRISE_CONSULT'     => '企业咨询',
            6 => "\\App\\Services\\Enterprise\\OrderService.NotFound",
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
];