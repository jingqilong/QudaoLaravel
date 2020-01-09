<?php


namespace App\Library\Members;


use App\Enums\OrderEnum;
use App\Services\Score\RecordService;

/**
 * Class Grade
 * @package App\Library\Members
 * @desc 会员等级的父类，注意
 * 假如算法都一样，实现算法的方法就放在此类中。
 * 如果算法有所不同，则实现算法的在不同的等级的类中实现。
 *
 */
class Grade
{
    /**
     * @desc 成员等级
     * @var string
     */
    private $member_grade;


    //按总额进行百分比奖励
    const  SCORE_REWARD_PERCENTAGE      =   0;
    //不按总额，每笔给予定额奖励
    const  SCORE_REWARD_FIXED_AMOUNT    =   1;

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

    /**
     * @param $member_id
     * @param $order_type
     * @param $amount
     * @return bool
     */
    public function addRewardScore($member_id,$order_type,$amount){
        $reward_params = config('member.reward_score');
        $reward_cfg = $reward_params[$order_type];
        $return = true;
        //一种业务可以奖励多种积分
        foreach($reward_cfg as $cfg){
            //计算要增加的积分：
            $reward_manner = $cfg['reward_manner'];
            if(self::SCORE_REWARD_PERCENTAGE == $reward_manner){ //按百分比奖励
                $action_score =  $amount * $cfg['score'][$this->member_grade];
            }else{
                $action_score =  $amount; //按固定积分额奖励
            }
            $explain  = '被推荐人' . OrderEnum::getOrderType($order_type) ."推荐奖励";
            $remark = "推荐奖励";
            //添加新记录
            $res = RecordService::increaseScore($cfg['score_type'],$action_score,$member_id,$remark,$explain,true);
            $return |= $res;
        }
        return $return;
    }
}