<?php
namespace App\Services\Score;


use App\Enums\MemberEnum;
use App\Enums\MessageEnum;
use App\Enums\ScoreEnum;
use App\Library\Time\Time;
use App\Repositories\MemberBaseRepository;
use App\Repositories\ScoreCategoryRepository;
use App\Repositories\ScoreRecordRepository;
use App\Repositories\ScoreRecordViewRepository;
use App\Services\BaseService;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
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
     * @param string $explain
     * @param bool $notify
     * @return bool
     */
    public function increaseScore($score_type, $score, $member_id, $remark,$explain = '获得',$notify = true){
        if (!ScoreCategoryRepository::exists(['id' => $score_type,'status' => ScoreEnum::OPEN])){
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
        if (!$score_id = ScoreRecordRepository::getAddId($add_arr)){
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
        if ($notify){
            if ($member = MemberBaseRepository::getOne(['id' => $score_record['member_id']])){
                $member_name = mb_substr($score_record['member_name'],0,1);
                $member_name = $member_name . MemberEnum::getSex($member['sex']);
                $sms_template =
                    MessageEnum::getTemplate(
                        MessageEnum::SCOREBOOKING,
                        'increaseScore',
                        ['member_name' => $member_name,'time' => date('Y年m月d日',time()),'explain' => $explain,'score_name' => $score_record['score_name'],'remnant_score' => $add_arr['remnant_score'],'score' => $score]
                    );
                #短信通知
                if (!empty($score_record['member_mobile'])){
                    $smsService = new SmsService();
                    $smsService->sendContent($score_record['member_mobile'],$sms_template);
                }
                $title = '积分赠送通知';
                #发送站内信
                SendService::sendMessage($score_record['member_id'],MessageEnum::SCOREBOOKING,$title,$sms_template,$score_id);
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
        if (!ScoreCategoryRepository::exists(['id' => $score_type,'status' => ScoreEnum::OPEN])){
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
        if (!$score_id = ScoreRecordRepository::getAddId($add_arr)){
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
        if ($member = MemberBaseRepository::getOne(['id' => $score_record['member_id']])){
            $member_name = mb_substr($score_record['member_name'],0,1);
            $member_name = $member_name . MemberEnum::getSex($member['sex']);
            $sms_template =
                MessageEnum::getTemplate(
                    MessageEnum::SCOREBOOKING,
                    'expenseScore',
                    ['member_name' => $member_name,'time' => date('Y年m月d日',time()),'score_name' => $score_record['score_name'],'remnant_score' => $add_arr['remnant_score'],'score' => $score]
                );
            #短信通知
            if (!empty($score_record['member_mobile'])){
                $smsService = new SmsService();
                $smsService->sendContent($score_record['member_mobile'],$sms_template);
            }
            $title = '积分赠送通知';
            #发送站内信
            SendService::sendMessage($score_record['member_id'],MessageEnum::SCOREBOOKING,$title,$sms_template,$score_id);
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
        if (!ScoreCategoryRepository::exists(['id' => $request['score_type'],'status' => ScoreEnum::OPEN])){
            $this->setMessage('积分类别不存在！');
            return false;
        }
        if (!MemberBaseRepository::exists(['id' => $request['member_id']])){
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
        $column = ['score_type','score_name','remnant_score','expense_rate','cashing_rate'];
        $where  = ['member_id' => $member_id,'latest' => ScoreEnum::LATEST,'status' => ScoreEnum::OPEN,'remnant_score' => ['<>',0]];
        if (!$list = ScoreRecordViewRepository::getList($where,$column)){
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

    /**
     * 获取积分分类列表
     * @param $request
     * @return bool|mixed|null
     */
    public function getScoreCategoryList($request)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        if (!$score_category_list = ScoreCategoryRepository::getList(['id' => ['<>',0]],['*'],'id','asc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $score_category_list = $this->removePagingField($score_category_list);
        if (empty($score_category_list['data'])){
            $this->setMessage('暂无数据！');
            return $score_category_list;
        }
        foreach ($score_category_list['data'] as &$value){
            $value['status_title'] = ScoreEnum::getStatus($value['status']);
        }
        $this->setMessage('获取成功！');
        return $score_category_list;
    }

    /**
     * 添加积分类型
     * @param $request
     * @return bool
     */
    public function addScoreCategory($request)
    {
        if (ScoreCategoryRepository::exists(['name' => $request['name']])){
            $this->setError('积分类型名称已被使用！');
            return false;
        }
        $add_arr = [
            'name'          => $request['name'],
            'expense_rate'  => $request['expense_rate'],
            'cashing_rate'  => $request['cashing_rate'],
            'is_cashing'    => $request['is_cashing'],
            'status'        => $request['status'],
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        DB::beginTransaction();
        if (ScoreCategoryRepository::getAddId($add_arr)){
            DB::rollBack();
            $this->setMessage('添加成功！');
            return true;
        }
        DB::rollBack();
        $this->setError('添加失败！');
        return false;
    }

    /**
     * 开启或关闭积分分类
     * @param $id
     * @return bool
     */
    public function openOrClose($id)
    {
        $where = ['id' => $id];
        if (!$score_category = ScoreCategoryRepository::getOne($where)){
            $this->setError('积分类型不存在！');
            return false;
        }
        $upd_arr = ['updated_at' => time()];
        if ($score_category['status'] == ScoreEnum::OPEN){
            if (ScoreCategoryRepository::getUpdId($where,array_merge($upd_arr,['status' => ScoreEnum::CLOSE]))){
                $this->setMessage('关闭成功！');
                return true;
            }
        }else{
            if (ScoreCategoryRepository::getUpdId($where,array_merge($upd_arr,['status' => ScoreEnum::OPEN]))){
                $this->setMessage('开启成功！');
                return true;
            }
        }
        $this->setError('操作失败！');
        return false;
    }

    /**
     * 下单详情获取可抵扣积分
     * @param $member_id
     * @param $goods_param
     * @param $goods_list
     * @param $total_price
     * @return array
     */
    public function getUsableScore($member_id, $goods_param, $goods_list, $total_price){
        /*
         * 1、遍历商品参数
         * 2、获取商品可兑换积分总额
         * 3、对比用户积分与可兑换积分总额
         */
        $member_score       = $this->getMemberScore($member_id);
        #商品可兑换积分
        $goods_scores       = [];
        foreach ($goods_param as $value){
            if ($goods = $this->searchArray($goods_list,'id',$value['goods_id'])){
                $goods = reset($goods);
                if (isset($goods['score_categories'])){
                    #当前商品可兑换积分种类
                    $score_categories = explode(',',trim($goods['score_categories'],','));
                    foreach ($score_categories as $category){
                        if (isset($goods_scores[$category])){
                            $goods_scores[$category]['total_score'] += $goods['score_deduction'] * $value['number'];
                        }else{
                            $goods_scores[$category]['score_type']  = $category;
                            $goods_scores[$category]['total_score'] = $goods['score_deduction'] * $value['number'];
                        }
                    }
                }
            }
        }
        $result = [];
        foreach ($member_score as $key => $score){
            if (isset($goods_scores[$score['score_type']])){
                $goods_score = $goods_scores[$score['score_type']];
                $result[$key]['score_type']     = $score['score_type'];
                $result[$key]['score_title']    = $score['score_title'];
                $result[$key]['expense_rate']   = $score['expense_rate'];
                if ($score['score'] > $goods_score['total_score']){
                    $result[$key]['usable_score'] = $goods_score['total_score'];
                }else{
                    $result[$key]['usable_score'] = $score['score'];
                }
                #防止出现兑换积分×汇率出现超过订单价格的情况，如果超过，则按订单总额的最大积分
                if (($result[$key]['usable_score'] * $score['expense_rate']) > $total_price){
                    $result[$key]['usable_score']   = floor($total_price / $score['expense_rate']);
                }
            }
        }
        $this->setMessage('获取成功！');
        return $result;
    }

    /**
     * 获取积分消费曲线（OA后台首页使用）
     * @param int $day
     * @return array|bool
     */
    public function getScoreStatisticsRecord(int $day){
        if ($day < 1){
            $this->setError('查看天数不能低于1天');
            return false;
        }
        if (!$score_types = ScoreCategoryRepository::getList(['status' => ScoreEnum::OPEN])){
            $this->setError('没有积分类别可查看');
            return false;
        }
        $res   = [];
        $today = Time::getStartStopTime('today');
        $where = [
            'action'        => ScoreEnum::EXPENSE,
            'score_type'    => ['in',array_column($score_types,'id')],
            'created_at'    => ['range',[$today['start'] - ($day * 86400),$today['end'] + ($day * 86400)]]
        ];
        $list = ScoreRecordRepository::getList($where,['score_type','action_score','created_at']) ?? [];
        #总积分消费记录
        for ($i = $day;$i >= 0;$i--){
            $date_time              = date('Y-m-d',strtotime('-'.$i.' day'));
            $res['总消费']['day'][] = $date_time;
            $start_time             = $today['start'] - ($i * 86400);
            $end_time               = $today['end'] - ($i * 86400);
            if ($records = $this->searchRangeArray($list,'created_at',[$start_time, $end_time])){
                $res['总消费']['count'][]    = $this->arrayFieldSum($records,'action_score');
            }else{
                $res['总消费']['count'][]    = 0;
            }
        }
        foreach ($score_types as $type){
            $type_name  = ScoreCategoryRepository::getField(['id' => $type],'name');
            for ($i = $day;$i >= 0;$i--){
                $date_time                  = date('Y-m-d',strtotime('-'.$i.' day'));
                $res[$type_name]['day'][]   = $date_time;
                if ($type_record = $this->searchArray($list,'score_type',$type['id'])){
                    $start_time  = $today['start'] - ($i * 86400);
                    $end_time    = $today['end'] - ($i * 86400);
                    if ($records = $this->searchRangeArray($type_record,'created_at',[$start_time, $end_time])){
                        $res[$type_name]['count'][]    = $this->arrayFieldSum($records,'action_score');
                    }else{
                        $res[$type_name]['count'][]    = 0;
                    }
                }else{
                    $res[$type_name]['count'][]    = 0;
                }
            }
        }
        $this->setMessage('获取成功！');
        return $res;
    }

    /**
     * 获取我的积分
     * @param $member_id
     * @return mixed
     */
    public function getMyScore($member_id)
    {
        $res = [
            'total_score'    => 0,
            'score_list'     => []
        ];
        if (!$score_categories = ScoreCategoryRepository::getList(['status' => ScoreEnum::OPEN],['*'],'id','asc')){
            $this->setMessage('暂无积分类别');
            return false;
        }
        $where = ['member_id' => $member_id,'latest' => ScoreEnum::LATEST,'score_type' => ['in',array_column($score_categories,'id')]];
        $score_list = ScoreRecordViewRepository::getList($where,['score_type','score_name','remnant_score'],'score_type','asc');
        foreach ($score_categories as $category){
            if (empty($score_list) || !$this->existsArray($score_list,'score_type',$category['id'])){
                $score_list[] = [
                    'score_type'    => $category['id'],
                    'score_name'    => $category['name'],
                    'remnant_score' => 0
                ];
                continue;
            }
//            if ($score = ScoreRecordViewRepository::getOne(array_merge($where,['score_type' => $category['id']]),['score_type','score_name','remnant_score'])){
//                $score_list[] = $score;continue;
//            }
//            $score_list[] = [
//                'score_type'    => $category['id'],
//                'score_name'    => $category['name'],
//                'remnant_score' => 0
//            ];
        }

        foreach ($score_list as $v){
            $res['total_score'] += $v['remnant_score'];
        }
        $res['score_list'] = $score_list;
        $this->setMessage('获取成功！');
        return $res;
    }

    /**
     * 获取我的积分列表
     * @param $request
     * @return bool|mixed|null
     */
    public function getMyRecordList($request)
    {
        $member     = Auth::guard('member_api')->user();
        $score_type = $request['score_type'] ?? null;
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $where      = ['member_id' => $member->id];
        if (!is_null($score_type)){
            $where['score_type'] = $score_type;
        }
        if (!$record_list = ScoreRecordRepository::getList($where,['action','action_score','explain','created_at'],'created_at','desc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $record_list = $this->removePagingField($record_list);
        if (empty($record_list['data'])){
            $this->setMessage('暂无数据！');
            return $record_list;
        }
        foreach ($record_list['data'] as &$value){
            if ($value['action'] == ScoreEnum::INCREASE){
                $value['action_score'] = '+ '.$value['action_score'];
            }
            if ($value['action'] == ScoreEnum::EXPENSE){
                $value['action_score'] = '- '.$value['action_score'];
            }
            $value['created_at'] = date('Y-m-d H:i:s',$value['created_at']);
            unset($value['action']);
        }
        $this->setMessage('获取成功！');
        return $record_list;
    }
}
            