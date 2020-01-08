<?php
namespace App\Services\Loan;


use App\Enums\LoanEnum;
use App\Enums\MessageEnum;
use App\Enums\ProcessCategoryEnum;
use App\Repositories\LoanPersonalRepository;
use App\Services\BaseService;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
use App\Traits\BusinessTrait;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PersonalService extends BaseService
{

    use HelpTrait,BusinessTrait;
    public $auth;

    /**
     * PrizeService constructor.
     * @param $auth
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }

    /**
     * 获取贷款订单列表 （前端显示）
     * @param array $data
     * @return mixed
     */
    public function getLoanList(array $data)
    {
        $memberInfo = $this->auth->user();
        $type       = $data['type'];
        $where      = ['user_id' => $memberInfo->id,'type' => $type,'deleted_at' => 0];
        if (!$list  = LoanPersonalRepository::getList($where)){
            $this->setMessage('没有数据！');
            return [];
        }
        foreach ($list as &$value)
        {
            $value['status_name']       =   LoanEnum::getStatus($value['status']);
            $value['type_name']         =   LoanEnum::getType($value['type']);
            $value['reservation_at']    =   date('Y-m-d',$value['reservation_at']);
            $value['created_at']        =   date('Y-m-d',$value['created_at']);
        }
        $this->setMessage('查找成功');
        return $list;
    }


    /**
     * 获取贷款订单列表 （后台显示）
     * @param array $data
     * @return mixed
     */
    public function getLoanOrderList(array $data)
    {
        $employee = Auth::guard('oa_api')->user();
        if (empty($data['asc']))  $data['asc'] = 1;
        $page           = $data['page'] ?? 1;
        $asc            = $data['asc'] ==  1 ? 'asc' : 'desc';
        $page_num       = $data['page_num'] ?? 20;
        $keywords       = $data['keywords'] ?? null;
        $column         = ['*'];
        $type           = $data['type'] ?? null;
        $status          = $data['status'] ?? null;
        $where          = ['deleted_at' => 0];
        if (!empty($type)) $where['type']  = $type;
        if (!empty($status)) $where['status'] = $status;
        if (!empty($keywords)){
            $keyword = [$keywords => ['name','mobile']];
            if (!$list = LoanPersonalRepository::search($keyword,$where,$column,$page,$page_num,'id',$asc)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = LoanPersonalRepository::getList($where,$column,'id',$asc,$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据');
            return [];
        }
        foreach ($list['data'] as &$value)
        {
            $value['type_name']         =   LoanEnum::getType($value['type']);
            $value['status_name']       =   LoanEnum::getStatus($value['status']);
            $value['price_name']        =   LoanEnum::getPrice($value['price']);
            $value['reservation_at']    =   date('Y-m-d H:m:s',$value['reservation_at']);
            $value['created_at']        =   date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at']        =   date('Y-m-d H:m:s',$value['updated_at']);
            #获取流程信息
            $value['progress'] = $this->getBusinessProgress($value['id'],ProcessCategoryEnum::LOAN_RESERVATION,$employee->id);
        }
        $this->setMessage('查找成功');
        return $list;
    }

    /**
     * 获取贷款订单信息
     * @param string $id
     * @return mixed
     */
    public function getLoanInfo(string $id)
    {
        $memberInfo = $this->auth->user();
        if (!$orderInfo= LoanPersonalRepository::getOne(['id' => $id,'user_id' => $memberInfo->id])){
            $this->setError('预约信息不存在!');
            return false;
        }
        $orderInfo['type_name']         =   LoanEnum::getType($orderInfo['type']);
        $orderInfo['status_name']       =   LoanEnum::getStatus($orderInfo['status']);
        $orderInfo['reservation_at']    =   date('Y-m-d H:m:s',$orderInfo['reservation_at']);
        $orderInfo['created_at']        =   date('Y-m-d H:m:s',$orderInfo['created_at']);
        $this->setMessage('查找成功');
        return $orderInfo;
    }


    /**
     * 添加贷款订单信息
     * @param array $data
     * @return mixed
     */
    public function addLoan(array $data)
    {
        $memberInfo = $this->auth->user();
        $add_arr  = [
            'user_id'         =>  $memberInfo->id,
            'name'            =>  $data['name'],
            'mobile'          =>  $data['mobile'],
            'price'           =>  $data['price'] ?? LoanEnum::MILLION,
            'ent_name'        =>  $data['ent_name'],
            'ent_title'       =>  $data['ent_title'],
            'address'         =>  $data['address'],
            'type'            =>  $data['type'],
            'remark'          =>  $data['remark'] ?? '',
            'status'          =>  LoanEnum::SUBMIT,
            'appointment'     =>  LoanEnum::PLATFORM,
            'reservation_at'  =>  strtotime($data['reservation_at']),
        ];
        if (LoanPersonalRepository::exists($add_arr)){
            $this->setError('您已预约，请勿重复预约!');
            return false;
        }
        $add_arr['created_at'] =  time();
        DB::beginTransaction();
        if (!$id = LoanPersonalRepository::getAddId($add_arr)){
            $this->setError('预约失败,请稍后重试！');
            DB::rollBack();
            return false;
        }
        $start_process_result = $this->addNewProcessRecord($id,ProcessCategoryEnum::LOAN_RESERVATION);
        if (100 == $start_process_result['code']){
            $this->setError('预约失败，请稍后重试！');
            DB::rollBack();
            return false;
        }
        $this->setMessage('恭喜你，预约成功');
        DB::commit();
        return true;
    }


    /**
     * 活动无需token  添加贷款订单信息
     * @param array $data
     * @return mixed
     */
    public function addLoanActivity(array $data)
    {
        if (!LoanEnum::isset($data['type'])){
              $this->setError('该推荐类型不存在');
        }
        $add_arr  = [
            'user_id'         =>  0,
            'name'            =>  $data['name'],
            'mobile'          =>  $data['mobile'],
            'price'           =>  $data['price'] ?? LoanEnum::MILLION,
            'ent_name'        =>  $data['ent_name'],
            'ent_title'       =>  $data['ent_title'],
            'address'         =>  $data['address'],
            'type'            =>  $data['type'],
            'remark'          =>  $data['remark'],
            'appointment'     =>  LoanEnum::ACTIVITY,
            'status'          =>  LoanEnum::SUBMIT,
            'reservation_at'  =>  strtotime($data['reservation_at']),
        ];
        if (LoanPersonalRepository::exists($add_arr)){
            $this->setError('您已预约，请勿重复预约!');
            return false;
        }
        $add_arr['created_at']     =  time();
        $add_arr['updated_at']     =  time();
        if (!$res = LoanPersonalRepository::getAddId($add_arr)){
            $this->setError('预约失败,请重试！');
            return false;
        }
        $this->setMessage('恭喜你，预约成功');
        return true;
    }

    /**
     * 用户 修改贷款订单信息
     * @param array $data
     * @return mixed
     */
    public function editLoan(array $data)
    {
        $id = $data['id'];
        if (!$loan = LoanPersonalRepository::getOne(['id' => $id,'deleted_at' => 0])){
            $this->setError('该订单不存在!');
        }
        if ($loan['status'] != LoanEnum::SUBMIT){
            $this->setError('预约已审核，请联系客服更改!');
            return false;
        }
        $add_arr  = [
            'name'            =>  $data['name'],
            'mobile'          =>  $data['mobile'],
            'price'           =>  $data['price'],
            'ent_name'        =>  $data['ent_name'],
            'ent_title'       =>  $data['ent_title'],
            'address'         =>  $data['address'] ?? '',
            'type'            =>  $data['type'],
            'remark'          =>  $data['remark'] ?? '',
            'status'          =>  LoanEnum::SUBMIT,
            'updated_at'      =>  time(),
            'reservation_at'  =>  strtotime($data['reservation_at']),
        ];
        if (!$res = LoanPersonalRepository::getUpdId(['id' => $id],$add_arr)){
            $this->setError('修改失败,请重试！');
            return false;
        }
        $this->setMessage('恭喜你，修改成功');
        return true;
    }


    /**
     * OA 修改贷款订单信息
     * @param array $data
     * @return mixed
     */
    public function updLoan(array $data)
    {
        if (!LoanPersonalRepository::exists(['id' => $data['id'],'deleted_at' => 0])){
            $this->setError('该订单不存在!');
        }
        if (!LoanEnum::isset($data['price'])){
            $this->setError('没有此价格的贷款!');
            return false;
        }
        $add_arr  = [
            'name'            =>  $data['name'],
            'mobile'          =>  $data['mobile'],
            'price'           =>  $data['price'],
            'ent_name'        =>  $data['ent_name'],
            'ent_title'       =>  $data['ent_title'],
            'address'         =>  $data['address'] ?? '',
            'type'            =>  $data['type'],
            'remark'          =>  $data['remark'] ?? '',
            'status'          =>  $data['status'],
            'updated_at'      =>  time(),
            'reservation_at'  =>  strtotime($data['reservation_at']),
        ];
        if (!$res = LoanPersonalRepository::getUpdId(['id' => $data['id']],$add_arr)){
            $this->setError('修改失败,请重试！');
            return false;
        }
        $this->setMessage('修改成功');
        return true;
    }

    /**
     * 软删除订单
     * @param integer $id
     * @return mixed
     */
    public function delLoan($id)
    {
        if (!LoanPersonalRepository::exists(['id' => $id,'deleted_at' => 0])){
            $this->setError('该订单不存在！');
            return false;
        }
        if (!LoanPersonalRepository::getUpdId(['id' => $id],['deleted_at' => time()])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功');
        return true;
    }

    /**
     * 根据ID查找贷款订单信息
     * @param $id
     * @return mixed
     */
    public function getLoanOrderInfo($id)
    {
        $employee = Auth::guard('oa_api')->user();
        if (!$info = LoanPersonalRepository::getOne(['id' => $id])){
            $this->setError('该订单不存在！');
            return false;
        }
        $info['type']           =  LoanEnum::getType($info['type']);
        $info['appointment']    =  LoanEnum::getAppointment($info['appointment']);
        $info['status']         =  LoanEnum::getStatus($info['status']);
        $info['price']          =  LoanEnum::getPrice($info['price']);
        $info['reservation_at'] =  empty($info['reservation_at']) ? '' : date('Y-m-d',$info['reservation_at']);
        $info['created_at']     =  empty($info['created_at']) ? '' : date('Y-m-d H:i:s',$info['created_at']);
        $info['updated_at']     =  empty($info['updated_at']) ? '' : date('Y-m-d H:i:s',$info['updated_at']);
        unset($info['deleted_at']);
        #获取流程进度
        $progress = $this->getProcessRecordList(['business_id' => $id,'process_category' => ProcessCategoryEnum::LOAN_RESERVATION]);
        if (100 == $progress['code']){
            $this->setError($progress['message']);
            return false;
        }
        #获取流程权限
        $process_permission = $this->getBusinessProgress($id,ProcessCategoryEnum::LOAN_RESERVATION,$employee->id);
        $this->setMessage('获取成功！');
        return [
            'details'               => $info,
            'progress'              => $progress['data'],
            'process_permission'    => $process_permission,
            #获取可操作的动作结果列表
            'action_result_list'    => $this->getActionResultList($process_permission['process_record_id'])
        ];
    }

    /**
     * 审核预约订单
     * @param $id
     * @param $audit
     * @return bool
     */
    public function auditLoan($id, $audit)
    {
        if (!$comment = LoanPersonalRepository::getOne(['id' => $id,'deleted_at' => 0])){
            $this->setError('预约订单不存在！');
            return false;
        }
        if ($comment['status'] > LoanEnum::SUBMIT){
            $this->setError('订单已审核!');
            return false;
        }
        $status = $audit == 1 ? LoanEnum::PASS : LoanEnum::NOPASS;
        if (!LoanPersonalRepository::getUpdId(['id' => $id],['status' => $status])){
            $this->setError('审核失败！');
            return false;
        }
        #通知用户
        if ($member = LoanPersonalRepository::getOne(['id' => $id])){
            $member_name = $comment['name'];
            $sms_template = [
                LoanEnum::PASS   => MessageEnum::getTemplate(MessageEnum::LOANBOOKING, 'auditPass', ['member_name' => $member_name]),
                LoanEnum::NOPASS => MessageEnum::getTemplate(MessageEnum::LOANBOOKING, 'auditNoPass', ['member_name' => $member_name]),
            ];
            #短信通知
            if (!empty($comment['mobile'])){
                $smsService = new SmsService();
                $smsService->sendContent($comment['mobile'],$sms_template[$status]);
            }
            $title = '贷款预约通知';
            #发送站内信
            SendService::sendMessage($comment['user_id'],MessageEnum::LOANBOOKING,$title,$sms_template[$status],$id);
        }
        $this->setMessage('审核成功！');
        return true;
    }


    /**
     * 获取预约统计数据（OA后台首页展示）
     * @return array
     */
    public static function getStatistics(){
        $total_count    = LoanPersonalRepository::count(['deleted_at' => 0]) ?? 0;
        $audit_count    = LoanPersonalRepository::count(['deleted_at' => 0,'status' => ['in',[LoanEnum::PASS,LoanEnum::NOPASS]]]) ?? 0;
        $no_audit_count = LoanPersonalRepository::count(['deleted_at' => 0,'status' => LoanEnum::SUBMIT]) ?? 0;
        $cancel_count   = 0;
        return [
            'total'     => $total_count,
            'audit'     => $audit_count,
            'no_audit'  => $no_audit_count,
            'cancel'    => $cancel_count
        ];
    }

    /**
     * 成员取消预约贷款
     * @param $request
     * @return bool
     */
    public function cancelLoan($request)
    {
        $member = $this->auth->user();
        if (!$loan = LoanPersonalRepository::getOne(['id' => $request['id'],'user_id' => $member->id])){
            $this->setError('没有预约信息!');
            return false;
        }
        if ($loan['status'] > LoanEnum::SUBMIT){
            $this->setError('预约已被审核，不能取消哦!');
            return false;
        }
        if (!LoanPersonalRepository::getUpdId(['id' => $loan['id']],['status' => LoanEnum::CANCEL])){
            $this->setError('取消预约失败!');
            return false;
        }
        $this->setMessage('取消成功!');
        return true;
    }

    /**
     * 获取申请人ID
     * @param $personal_id
     * @return mixed
     */
    public function getCreatedUser($personal_id){
        return LoanPersonalRepository::getField(['id' => $personal_id],'user_id');
    }

    /**
     * 返回流程中的业务列表
     * @param $repository_ids
     * @return mixed
     */
    public function getProcessBusinessList($repository_ids){
        if (empty($repository_ids)){
            return [];
        }
        $column     = ['id','user_id','name','mobile','price'];
        if (!$order_list = LoanPersonalRepository::getAssignList($repository_ids,$column)){
            return [];
        }
        $result_list = [];
        foreach ($order_list as $value){
            $result_list[] = [
                'id'            => $value['id'],
                'name'          => '贷款'.LoanEnum::getPrice($value['price']),
                'member_id'     => $value['user_id'],
                'member_name'   => $value['name'],
                'member_mobile' => $value['mobile'],
            ];
        }
        return $result_list;
    }
}
            