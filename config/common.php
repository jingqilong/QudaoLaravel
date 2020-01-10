<?php
return [
    //管家联系方式
    'contact' => [
        'mobile' => '13322225555',
        'we-chat'=> '13322225555'
    ],


    //短信
    'sms' => [
        //验证码有效期，单位：秒
        'ttl' => 300,
        //验证码长度
        'long' => 4
    ],

    //测试用户测试权限有效期，单位：小时
    'test_user_ttl' => 24,

    //测试使用的推荐码
    'test_referral_code' => 'DAICHIPLUS',

    //员工自己修改密码每日上限次数
    'employee_self_edit_password_number' => 3,
];