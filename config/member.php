<?php


return [
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
    ]

];