<?php


return [
    /**
     * 积分奖励配置
     */
    'reward_score' =>[
        '1'=>[  //业务类型为1的
                [
                    'score_type' => 1, //奖励的积分类型  1:会员升级，2：活动报名，3：精选生活订单，4：商城购物
                    'reward_manner' => 0,      //0： 按总额进行百分比奖励  //1： 不按总额，每笔给予定额奖励
                    'score' =>[ //奖励的积分
                        '1' => '0',
                        '2' => '0',
                        '3' => '0',
                        '4' => '0',
                        '5' => '0',
                        '6' => '0',
                        '7' => '0',
                        '8' => '0',
                        '9' => '0',
                        'default' => '0',
                ],
            ]

        ],
        '2'=>[  //业务类型为2的
                [
                    'score_type' => 2, //奖励的积分类型  1:会员升级，2：活动报名，3：精选生活订单，4：商城购物
                    'reward_manner' => 0,      //0： 按总额进行百分比奖励  //1： 不按总额，每笔给予定额奖励
                    'score' =>[ //奖励的积分
                        '1' => '0',
                        '2' => '0',
                        '3' => '0',
                        '4' => '0',
                        '5' => '0',
                        '6' => '0',
                        '7' => '0',
                        '8' => '0',
                        '9' => '0',
                        'default' => '0',
                ]
            ],

        ],
        '3'=>[  //业务类型为3的
                [
                    'score_type' => 1, //奖励的积分类型  1:会员升级，2：活动报名，3：精选生活订单，4：商城购物
                    'reward_manner' => 0,      //0： 按总额进行百分比奖励  //1： 不按总额，每笔给予定额奖励
                    'score' =>[ //奖励的积分
                        '1' => '0',
                        '2' => '0',
                        '3' => '0',
                        '4' => '0',
                        '5' => '0',
                        '6' => '0',
                        '7' => '0',
                        '8' => '0',
                        '9' => '0',
                        'default' => '0',
                ]
             ],

        ],
        '4'=>[ //业务类型为4的
                [
                    'score_type' => 1, //奖励的积分类型  1:会员升级，2：活动报名，3：精选生活订单，4：商城购物
                    'reward_manner' => 0,      //0： 按总额进行百分比奖励  //1： 不按总额，每笔给予定额奖励
                    'score' =>[ //奖励的积分
                        '1' => '0',
                        '2' => '0',
                        '3' => '0',
                        '4' => '0',
                        '5' => '0',
                        '6' => '0',
                        '7' => '0',
                        '8' => '0',
                        '9' => '0',
                        'default' => '0',
                ]
            ],

        ],
    ],
    /**
     * 成员折扣，目前只是活动报名费折扣的配置
     */
    'discount_ratio' =>[
        '1' => '1',
        '2' => '1',
        '3' => '1',
        '4' => '1',
        '5' => '1',
        '6' => '1',
        '7' => '1',
        '8' => '1',
        '9' => '1',
        'default' => '1',
    ],

    /**
     * 成员折扣，目前只是活动报名费折扣的配置
     */
    'reward' =>[
        '1' => '1',
        '2' => '1',
        '3' => '1',
        '4' => '1',
        '5' => '1',
        '6' => '1',
        '7' => '1',
        '8' => '1',
        '9' => '1',
        'default' => '1',
    ],
    /**
     * 成员类：如果成员有不同算法，则放到不同的类中，这里中配置每一级成员类的地方
     */
    'member_class'=>[
        'default' => App\Library\Members\Grades\GradeA::class,
        '1' => App\Library\Members\Grades\GradeB::class,
        '2' => App\Library\Members\Grades\GradeC::class,
        '3' => App\Library\Members\Grades\GradeD::class,
        '4' => App\Library\Members\Grades\GradeE::class,
        '5' => App\Library\Members\Grades\GradeF::class,
        '6' => App\Library\Members\Grades\GradeG::class,
        '7' => App\Library\Members\Grades\GradeH::class,
        '8' => App\Library\Members\Grades\GradeI::class,
        '9' => App\Library\Members\Grades\GradeJ::class,
    ]

];