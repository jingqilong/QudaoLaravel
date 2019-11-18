<?php
namespace App\Services\Score;


use App\Enums\MemberEnum;
use App\Enums\ScoreEnum;
use App\Repositories\MemberRepository;
use App\Repositories\ScoreCategoryRepository;
use App\Repositories\ScoreRecordRepository;
use App\Repositories\ScoreRecordViewRepository;
use App\Services\BaseService;
use App\Services\Common\SmsService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\DB;

class RecordService extends BaseService
{
    use HelpTrait;
    /**
     * 增加积分
     * @param $score_type
     * @param $score
     * @param $member_id
     * @param $remark
     * @return bool
     */
    public function increaseScore($score_type, $score, $member_id, $remark,$explain = '获得'){
        if (!ScoreCategoryRepository::exists(['type' => $score_type,'status' => ScoreEnum::OPEN])){
            $this->setMessage('积分类别不存在！');
            return false;
        }
        $score_record = ScoreRecordViewRepository::getOrderOne(['score_type' => $score_type,'member_id' => $member_id],'created_at','desc');
        $add_arr = [
            'member_id'             => $member_id,
            'score_type'            => $score_type,
            'remnant_score'         => ($score_record['remnant_score'] ?? 0) + $score,
            'before_action_score'   => $score_record['remnant_score'] ?? 0,
            'action'                => ScoreEnum::INCREASE,
            'action_score'          => $score,
            'explain'               => $remark,
            'latest'                => ScoreEnum::LATEST,
            'created_at'            => time()
        ];
        DB::beginTransaction();
        if (!ScoreRecordRepository::getAddId($add_arr)){
            $this->setError('操作失败！');
            DB::rollBack();
            return false;
        }
        if (isset($score_record['id'])){
            if (!ScoreRecordRepository::getUpdId(['id' => $score_record['id']],['latest' => ScoreEnum::OLD])){
                $this->setError('操作失败！');
                DB::rollBack();
                return false;
            }
        }
        //通知用户
        if ($member = MemberRepository::getOne(['m_id' => $score_record['member_id']])){
            $member_name = mb_substr($score_record['member_name'],0,1);
            $member_name = $member_name . MemberEnum::getSex($member['m_sex']);
            #短信通知
            if (!empty($score_record['member_mobile'])){
                $smsService = new SmsService();
                $sms_template = '尊敬的'.$member_name.',您于'.date('Y年m月d日',time()).'在渠道PLUS资源共享平台'.$explain.$score_record['score_name'].$score.'分，当前可用'.$add_arr['remnant_score'].'分。';
                $smsService->sendContent($score_record['member_mobile'],$sms_template);
            }
        }
        $this->setMessage('操作成功！');
        DB::commit();
        return true;
    }

    /**
     * 消费积分
     * @param $score_type
     * @param $score
     * @param $member_id
     * @param $remark
     * @return bool
     */
    public function expenseScore($score_type, $score, $member_id, $remark){
        if (!ScoreCategoryRepository::exists(['type' => $score_type,'status' => ScoreEnum::OPEN])){
            $this->setMessage('积分类别不存在！');
            return false;
        }
        if (!$score_record = ScoreRecordViewRepository::getOrderOne(['score_type' => $score_type,'member_id' => $member_id],'created_at','desc')){
            $this->setError('无积分可消费！');
            return false;
        }
        if ($score_record['remnant_score'] < $score){
            $this->setError('剩余积分不足！');
            return false;
        }
        $add_arr = [
            'member_id'             => $member_id,
            'score_type'            => $score_type,
            'remnant_score'         => $score_record['remnant_score'] - $score,
            'before_action_score'   => $score_record['remnant_score'],
            'action'                => ScoreEnum::EXPENSE,
            'action_score'          => $score,
            'explain'               => $remark,
            'latest'                => ScoreEnum::LATEST,
            'created_at'            => time()
        ];
        DB::beginTransaction();
        if (!ScoreRecordRepository::getAddId($add_arr)){
            $this->setError('操作失败！');
            DB::rollBack();
            return false;
        }
        if (!ScoreRecordRepository::getUpdId(['id' => $score_record['id']],['latest' => ScoreEnum::OLD])){
            $this->setError('操作失败！');
            DB::rollBack();
            return false;
        }
        //通知用户
        if ($member = MemberRepository::getOne(['m_id' => $score_record['member_id']])){
            $member_name = mb_substr($score_record['member_name'],0,1);
            $member_name = $member_name . MemberEnum::getSex($member['m_sex']);
            #短信通知
            if (!empty($score_record['member_mobile'])){
                $smsService = new SmsService();
                $sms_template = '尊敬的'.$member_name.',您于'.date('Y年m月d日',time()).'在渠道PLUS资源共享平台消费'.$score_record['score_name'].$score.'分，当前可用'.$add_arr['remnant_score'].'分。';
                $smsService->sendContent($score_record['member_mobile'],$sms_template);
            }
        }
        $this->setMessage('操作成功！');
        DB::commit();
        return true;
    }

    /**
     * 获取会员积分剩余列表
     * @param $request
     * @return bool|mixed|null
     */
    public function getScoreRecordList($request){
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $score_type = $request['score_type'] ?? null;
        $latest     = $request['latest'] ?? null;
        $keywords   = $request['keywords'] ?? null;
        $where      = ['id' => ['<>',0]];
        if (!is_null($score_type)){
            $where['score_type'] = $score_type;
        }
        if (!is_null($latest)){
            $where['latest'] = ScoreEnum::LATEST;
        }
        if (!is_null($keywords)){
            $keywords = [$keywords => ['member_name','member_mobile','explain']];
            if (!$list = ScoreRecordViewRepository::search($keywords,$where,['*'],$page,$page_num,'id','desc')){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = ScoreRecordViewRepository::getList($where,['*'],'id','desc',$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }
        $list  = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        foreach ($list['data'] as &$value){
            $value['action_title'] = ScoreEnum::getAction($value['action']);
            $value['latest_title'] = ScoreEnum::getLatest($value['latest']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 赠送积分给会员
     * @param $request
     * @return bool
     */
    public function giveScore($request)
    {
        if (!ScoreCategoryRepository::exists(['type' => $request['score_type'],'status' => ScoreEnum::OPEN])){
            $this->setMessage('积分类别不存在！');
            return false;
        }
        if (!MemberRepository::exists(['m_id' => $request['member_id']])){
            $this->setMessage('会员不存在！');
            return false;
        }
        if ($this->increaseScore($request['score_type'],$request['score'],$request['member_id'],$request['explain'])){
            $this->setMessage('赠送成功！');
            return true;
        }
        return false;
    }

    /**
     * 获取会员各类积分列表
     * @param $member_id
     * @return array|null
     */
    public function getMemberScore($member_id)
    {
        $column = ['score_type','score_name','remnant_score'];
        if (!$list = ScoreRecordViewRepository::getList(['member_id' => $member_id,'latest' => ScoreEnum::LATEST],$column)){
            $this->setMessage('暂无积分可使用！');
            return [];
        }
        foreach ($list as &$value){
            $value['score']         = $value['remnant_score'];
            $value['score_title']   = $value['score_name'];
            unset($value['remnant_score'],$value['score_name']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            