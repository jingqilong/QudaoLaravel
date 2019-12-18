<?php
namespace App\Services\Member;


use App\Enums\GradeOrderEnum;
use App\Enums\MemberEnum;
use App\Enums\MemberGradeEnum;
use App\Enums\MessageEnum;
use App\Enums\OrderEnum;
use App\Repositories\MemberBaseRepository;
use App\Repositories\MemberGradeDefineRepository;
use App\Repositories\MemberGradeOrdersRepository;
use App\Repositories\MemberGradeOrdersViewRepository;
use App\Repositories\MemberGradeRepository;
use App\Repositories\MemberOrdersRepository;
use App\Services\BaseService;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GradeOrdersService extends BaseService
{
    use HelpTrait;

    /**
     * 会员等级申请提交
     * @param $request
     * @return bool
     */
    public function upgradeApply($request)
    {
        $member = Auth::guard('member_api')->user();
        if (!$grade = MemberGradeDefineRepository::getOne(['iden' => $request['grade'],'status' => MemberGradeEnum::ENABLE])){
            $this->setError('会员等级不存在!');
            return false;
        }
        $now_grade  = 0;//会员当前等级
        if ($member_grade = MemberGradeRepository::getOne(['user_id' => $member->id,'status' => MemberEnum::PASS])){
            $now_grade = $member_grade['grade'];
        }
        if (MemberGradeOrdersRepository::exists(['member_id' => $member->id,'status' => GradeOrderEnum::PAYMENT,'audit' => ['<>',GradeOrderEnum::NOPASS]])){
            $this->setError('您还有未完成的申请，请完成后再申请!');
            return false;
        }
        DB::beginTransaction();
        $orderService = new OrdersService();
        //创建订单
        if (!$order_no = $orderService->placeOrder($member->id,$request['time'] * $grade['amount'] * 100,OrderEnum::MEMBERRECHARGE)){
            $this->setError($orderService->error);
            DB::rollBack();
            return false;
        }
        $add_arr = [
            'member_id'     => $member->id,
            'previous_grade'=> $now_grade,
            'grade'         => $request['grade'],
            'amount'        => $request['time'] * $grade['amount'],
            'validity'      => $request['time'],
            'order_no'      => $order_no,
            'status'        => GradeOrderEnum::PAYMENT,
            'audit'         => GradeOrderEnum::PENDING,
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (!MemberGradeOrdersRepository::getAddId($add_arr)){
            $this->setError('申请失败！');
            DB::rollBack();
            return false;
        }
        $this->setMessage('提交申请成功！');
        DB::commit();
        return true;
    }

    /**
     * 获取等级申请列表
     * @param $request
     * @return bool|mixed|null
     */
    public function getUpgradeApplyList($request)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $keywords   = $request['keywords'] ?? null;
        $grade      = $request['grade'] ?? null;
        $status     = $request['status'] ?? null;
        $audit      = $request['audit'] ?? null;
        $where      = ['id' => ['>',0]];
        $column     = ['id','member_id','mobile','ch_name','previous_grade','previous_grade_title','grade','grade_title','amount','validity','order_no','status','audit','created_at'];
        if (!is_null($grade)){
            $where['grade'] = $grade;
        }
        if (!is_null($status)){
            $where['grade'] = $grade;
        }
        if (!is_null($audit)){
            $where['grade'] = $grade;
        }
        if (!empty($keywords)){
            $keyword = [$keywords => ['mobile','ch_name']];
            if (!$list = MemberGradeOrdersViewRepository::search($keyword,$where,$column,$page,$page_num,'created_at','desc')){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = MemberGradeOrdersViewRepository::getList($where,$column,'created_at','desc',$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        foreach ($list['data'] as &$value){
            $value['status_title'] = GradeOrderEnum::getStatus($value['status']);
            $value['audit_title']  = GradeOrderEnum::getAuditStatus($value['audit']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 会员升级申请审核
     * @param $request
     * @return bool
     */
    public function auditApply($request)
    {
        if (!$apply = MemberGradeOrdersViewRepository::getOne(['id' => $request['id']])){
            $this->setError('申请记录不存在！');
            return false;
        }
        if ($apply['audit'] !== GradeOrderEnum::PENDING){
            $this->setError('该申请已审核完成，请勿重复审核！');
            return false;
        }
        $upd = ['audit' => $request['audit'],'updated_at' => time()];
        if (!MemberGradeOrdersRepository::getUpdId(['id' => $request['id']],$upd)){
            $this->setError('审核失败！');
            return false;
        }
        //如果审核未通过，设置订单状态为已关闭
        if ($request['audit'] == GradeOrderEnum::NOPASS){
            MemberOrdersRepository::getUpdId(['order_no' => $apply['order_no']],['status' => OrderEnum::STATUSCLOSE,'updated_at' => time()]);
        }
        //通知用户
        $member_name = $apply['ch_name'] . MemberEnum::getSex($apply['sex']);
        $sms_template = [
            GradeOrderEnum::PASS    => '尊敬的'.$member_name.'您好！您的等级升级申请已通过审核，我们的负责人稍后会跟您联系，请您耐心等待！',
            GradeOrderEnum::NOPASS  => '尊敬的'.$member_name.'您好！您的等级升级申请没能通过审核，如有疑问请联系客服：021-53067999！',
        ];
        #短信通知
        if (!empty($apply['mobile'])){
            $smsService = new SmsService();
            $smsService->sendContent($apply['mobile'],$sms_template[$request['audit']]);
        }
        $title = '会员等级申请通知';
        #发送站内信
        SendService::sendMessage($apply['member_id'],MessageEnum::SYSTEMNOTICE,$title,$sms_template[$request['audit']],$request['id']);
        $this->setMessage('审核成功！');
        return true;
    }

    /**
     * 设置会员升级申请状态
     * @param $request
     * @return bool
     */
    public function setApplyStatus($request)
    {
        $payment_amount = $request['payment_amount'] ?? 0;
        if (!$apply = MemberGradeOrdersViewRepository::getOne(['id' => $request['id']])){
            $this->setError('申请记录不存在！');
            return false;
        }
        if ($apply['audit'] !== GradeOrderEnum::PASS){
            $this->setError('该申请未通过审核，不能进行此操作！');
            return false;
        }
        if ($apply['status'] !== GradeOrderEnum::PAYMENT){
            $this->setError('该申请'.GradeOrderEnum::getStatus($apply['status']).'，不能再操作！');
            return false;
        }
        $upd = [
            'status' => $request['status'],
            'updated_at' => time()
        ];
        DB::beginTransaction();
        //如果设置状态已支付，则更新订单状态
        if ($request['status'] == GradeOrderEnum::EVALUATION){
            if (!MemberOrdersRepository::getUpdId(
                ['order_no' => $apply['order_no']],['status' => OrderEnum::STATUSSUCCESS,'payment_amount' => $payment_amount * 100,'updated_at' => time()])
            ){
                $this->setError('设置失败！');
                DB::rollBack();
                return false;
            }
            //TODO 此处进行升级成员等级
        }
        if (!MemberGradeOrdersRepository::getUpdId(['id' => $request['id']],$upd)){
            $this->setError('设置失败！');
            DB::rollBack();
            return false;
        }
        //通知用户
        $member_name = $apply['ch_name'] . MemberEnum::getSex($apply['sex']);
        $sms_template = [
            GradeOrderEnum::EVALUATION  => '尊敬的'.$member_name.'您好！您已成功升级为'.$apply['grade_title'].'，进入微信公众号即可查看享有权益。如有疑问请联系客服：021-53067999！',
            GradeOrderEnum::CANCELED    => '尊敬的'.$member_name.'您好！您的等级升级申请取消成功，如有需要，进入微信公众号即可再次申请。如有疑问请联系客服：021-53067999！',
        ];
        #短信通知
        if (!empty($apply['mobile'])){
            $smsService = new SmsService();
            $smsService->sendContent($apply['mobile'],$sms_template[$request['status']]);
        }
        $title = '会员等级申请通知';
        #发送站内信
        $sms_template = [
            GradeOrderEnum::EVALUATION  => '尊敬的'.$member_name.'您好！您已成功升级为'.$apply['grade_title'].'，点击【我的 -> 成员权益】即可查看享有权益。如有疑问请联系客服：021-53067999！',
            GradeOrderEnum::CANCELED    => '尊敬的'.$member_name.'您好！您的等级升级申请取消成功，如有需要，点击【我的 -> 成员权益】即可再次申请。如有疑问请联系客服：021-53067999！',
        ];
        SendService::sendMessage($apply['member_id'],MessageEnum::SYSTEMNOTICE,$title,$sms_template[$request['status']],$apply['id']);
        $this->setMessage('设置成功！');
        DB::commit();
        return true;
    }
}
            