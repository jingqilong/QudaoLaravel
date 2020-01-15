<?php
namespace App\Services\Loan;


use App\Enums\LoanEnum;
use App\Enums\MessageEnum;
use App\Enums\ProcessCategoryEnum;
use App\Repositories\LoanPersonalRepository;
use App\Repositories\MemberBaseRepository;
use App\Services\BaseService;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
use App\Traits\BusinessTrait;
use App\Traits\HelpTrait;
use EasyWeChat\Kernel\Support\Arr;
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
            $value['status_name']    =   LoanEnum::getStatus($value['status']);
            $value['type_name']      =   LoanEnum::getType($value['type']);
            $value['reservation_at'] =   date('Y年m月d日',$value['reservation_at']);
            $value['created_at']     =   date('Y年m月d日',$value['created_at']);
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
        if (empty($data['asc']))  $data['asc'] = 2;
        $asc        = $data['asc'] ==  1 ? 'asc' : 'desc';
        $page       = $data['page'] ?? 1;
        $page_num   = $data['page_num'] ?? 20;
        $keywords   = $data['keywords'] ?? null;
        $column     = ['*'];
        $where      = ['deleted_at' => 0];
        $where_arr  = Arr::only($data,['type','status']);
        foreach ($where_arr as $key => $value){
            if (!is_null($value)){
                $where[$key] = $value;
            }
        }
        if (!is_null($keywords)){
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
            return $list;
        }
        $list['data'] = MemberBaseRepository::bulkHasOneWalk(
            $list['data'],
            ['from' => 'user_id' ,'to' => 'id'],
            ['id','ch_name','mobile'],
            [],
            function ($src_item,$member_base_items){
                $src_item['recommend_name']   = $member_base_items['ch_name'];
                $src_item['recommend_mobile'] = $member_base_items['mobile'];
                return $src_item;
            }
        );
        foreach ($list['data'] as &$value)
        {
            $value['type_name']         =   LoanEnum::getType($value['type']);
            $value['status_name']       =   LoanEnum::getStatus($value['status']);
            $value['price_name']        =   $value['price'] . '万元';
            $value['reservation_at']    =   date('Y-m-d H:m:s',$value['reservation_at']);
            $value['created_at']        =   date('Y-m-d H:m:s',$value['created_at']);
            unset( $value['updated_at'], $value['deleted_at'], $value['deleted_at']);
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
        $column = ['id','name','mobile','price','type','ent_name','ent_title','address','appointment','status','remark','created_at','reservation_at'];
        if (!$orderInfo= LoanPersonalRepository::getOne(['id' => $id,'user_id' => $memberInfo->id],$column)){
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
        $add_arr    = Arr::only($data,['name','mobile','price','ent_name','company_address','ent_title','type','remark']);
        $handle_arr = [
            'user_id'         =>  $memberInfo->id,
            'address'         =>  '静安区延安西路300号10楼1001室',
            'status'          =>  LoanEnum::SUBMIT,
            'appointment'     =>  LoanEnum::PLATFORM,
            'reservation_at'  =>  strtotime($data['reservation_at']),
            'created_at'      =>  time(),
            'updated_at'      =>  time(),
        ];
        if ($handle_arr['reservation_at'] < time()){
            $this->setError('不能预约已经逝去的时间哦!');
            return false;
        }
        if (LoanPersonalRepository::exists(array_merge($handle_arr,$add_arr))){
            $this->setError('您已预约，请勿重复预约!');
            return false;
        }
        DB::beginTransaction();
        if (!$id = LoanPersonalRepository::getAddId(array_merge($handle_arr,$add_arr))){
            $this->setError('预约失败,请稍后重试！');
            DB::rollBack();
            return false;
        }
        #开启流程
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
        $add_arr    = Arr::only($data,['name','mobile','price','ent_name','company_address','ent_title','type','remark']);
        $handle_arr = [
            'user_id'         =>  0,
            'address'         =>  '静安区延安西路300号10楼1001室',
            'status'          =>  LoanEnum::SUBMIT,
            'appointment'     =>  LoanEnum::ACTIVITY,
            'reservation_at'  =>  strtotime($data['reservation_at']),
            'created_at'      =>  time(),
            'updated_at'      =>  time(),
        ];
        if ($handle_arr['reservation_at'] < time()){
            $this->setError('不能预约已经逝去的时间哦!');
            return false;
        }
        if (LoanPersonalRepository::exists(array_merge($handle_arr,$add_arr))){
            $this->setError('您已预约，请勿重复预约!');
            return false;
        }
        if (!LoanPersonalRepository::getAddId(array_merge($handle_arr,$add_arr))){
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
        if (!$status = LoanPersonalRepository::getField(['id' => $data['id']],'status')){
            $this->setError('该订单不存在!');
        }
        if ($status > LoanEnum::SUBMIT){
            $this->setError('预约已审核，请联系客服更改!');
            return false;
        }
        $add_arr    = Arr::only($data,['name','mobile','price','company_address','ent_name','ent_title','type','remark']);
        $handle_arr = [
            'status'          =>  LoanEnum::SUBMIT,
            'appointment'     =>  LoanEnum::PLATFORM,
            'reservation_at'  =>  strtotime($data['reservation_at']),
            'updated_at'      =>  time(),
        ];
        if (!LoanPersonalRepository::getUpdId(['id' => $data['id']],array_merge($handle_arr,$add_arr))){
            $this->setError('修改失败！');
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
        $add_arr    = Arr::only($data,['name','mobile','price','company_address','type','status','ent_name','ent_title','address','remark']);
        $handle_arr = [
            'appointment'     =>  LoanEnum::PLATFORM,
            'updated_at'      =>  time(),
            'reservation_at'  =>  strtotime($data['reservation_at']),
        ];
        if (!LoanPersonalRepository::getUpdId(['id' => $data['id']],array_merge($handle_arr,$add_arr))){
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
        if (!$info = LoanPersonalRepository::getOne(['id' => $id,'deleted_at' => 0])){
            $this->setError('该订单不存在！');
            return false;
        }
        $info['type']           =  LoanEnum::getType($info['type']);
        $info['appointment']    =  LoanEnum::getAppointment($info['appointment']);
        $info['status']         =  LoanEnum::getStatus($info['status']);
        $info['reservation_at'] =  empty($info['reservation_at']) ? '' : date('Y-m-d',$info['reservation_at']);
        $info['created_at']     =  empty($info['created_at']) ? '' : date('Y-m-d H:i:s',$info['created_at']);
        unset($info['updated_at'],$info['deleted_at']);
        return $this->getBusinessDetailsProcess($info,ProcessCategoryEnum::LOAN_RESERVATION,$employee->id);
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
            