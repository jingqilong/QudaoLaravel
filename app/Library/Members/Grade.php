<?php


namespace App\Library\Members;

use App\Repositories\ScoreRecordRepository;
use App\Enums\OrderEnum;
use Tolawho\Loggy\Facades\Loggy;

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
            //获取最后的记录 理论上，不需要用 latest 参数
            $member_score = ScoreRecordRepository::getFirst(
                ['member_id'=>$member_id,'score_type'=>$cfg['score_type']],
                ['id'],
                ['desc']
            );
            if(!$member_score){ //还没有积分记录时，给默认数据
                $member_score['remnant_score'] = 0;
                $member_score['id'] = 0;
            }
            $new_data = [
                'member_id'=>$member_id,
                'score_type'=> $cfg['score_type'],
                'remnant_score' =>0,
                'before_action_score' => $member_score['remnant_score'],
                'action' => 0, //增加积分
                'action_score' => 0,
                'explain'=>'',
                'latest'=>1,
                'created_at' => time()
            ];
            //计算要增加的积分：
            $reward_manner = $cfg['reward_manner'];
            if(self::SCORE_REWARD_PERCENTAGE == $reward_manner){ //按百分比奖励
                $new_data['action_score'] =  $amount * $cfg['score'][$this->member_grade];
            }else{
                $new_data['action_score'] =  $amount; //按固定积分额奖励
            }
            $new_data['remnant_score']   += $new_data['action_score'];
            $new_data['explain']  = '被推荐人' . OrderEnum::getOrderType($order_type) ."推荐奖励";
            //添加新记录
            $res =  ScoreRecordRepository::getAddId($new_data);
            if($res > 0 ) {
                if(0 !== $member_score['id']){ //如果有旧记录，更新旧记录的最新状态
                    $member_score['latest']     = 0;
                    $id = $member_score['id'];
                    unset( $member_score['id']);
                    $ret =  ScoreRecordRepository::update(['id'=>$id],$member_score);
                    if(false === $ret){
                        Loggy::write('error','更新积分记录最新状态失败！',[$id,$member_score]);
                        $return = false;
                    }
                }
            }else{
                Loggy::write('error','添加积分记录失败！',$new_data);
                $return = false;
            }
            $return |=  true;
        }
        return $return;
    }
}