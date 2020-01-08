<?php


namespace App\Library\Members;

/**
 * Class Grade
 * @package App\Library\Members
 * @desc 会员等级的父类，假如算法都一样，基本算法放在此类中。如果不同，则在不同的等级中实现。
 *
 */
class Grade
{
    /**
     * @desc 成员等级
     * @var string
     */
    private $member_grade;

    /**
     * @desc  成员算法集中处理类
     * Member constructor.
     */
    public function __construct()
    {
    }

    /**
     * @desc 静态创建对象
     * @param $member_grade
     */
    public function setGrade($member_grade)
    {
        $this->member_grade = $member_grade;
    }

    /**
     * @return string
     */
    public function getGrade(){
        return $this->member_grade;
    }

    /**
     * @desc 计算成员优惠价。（目前仅用于活动报名）
     * @param $amount
     * @return float|int
     */
    public function discount($amount){
        $ratios = config('member.discount_ratio');
        $ratio = $ratios[$this->member_grade];
        return $amount*$ratio;
    }
}