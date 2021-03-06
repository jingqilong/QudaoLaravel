<?php
return [
    //管家联系方式
    'contact' => [
        'mobile' => '13322225555',
        'we-chat'=> '13322225555'
    ],


    //短信验证码
    'sms' => [
        //验证码有效期，单位：秒
        'ttl' => 600,
        //验证码长度
        'length' => 4
    ],

    //邮件验证码
    'email' => [
        //验证码有效期，单位：秒
        'ttl' => 600,
        //验证码长度
        'length' => 4
    ],

    //测试用户测试权限有效期，单位：小时
    'test_user_ttl' => 24,

    //测试使用的推荐码
    'test_referral_code' => 'DAICHIPLUS',

    //员工自己修改密码每日上限次数
    'employee_self_edit_password_number' => 3,

    //员工自己修改手机号密码错误上限次数【每日】
    'employee_self_edit_mobile_error_number' => 3,

    //员工自己修改邮箱密码错误上限次数【每日】
    'employee_self_edit_email_error_number' => 3,

    'shop' => [
        //订单收货时长，单位天，超过收货时长后，自动收货
        'order_receive_ttl' => 14,
    ],
];