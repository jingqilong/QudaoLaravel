<?php
namespace App\Services\Loan;


use App\Enums\LoanEnum;
use App\Enums\MemberEnum;
use App\Repositories\LoanPersonalRepository;
use App\Repositories\MemberRepository;
use App\Services\BaseService;
use App\Services\Common\SmsService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;

class PersonalService extends BaseService
{

    use HelpTrait;
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
        $where      = ['user_id' => $memberInfo['m_id'],'type' => $type,'deleted_at' => 0];
        if (!$list  = LoanPersonalRepository::getList($where)){
            $this->setMessage('没有数据！');
            return [];
        }
        foreach ($list as &$value)
        {
            $value['status_name']       =   LoanEnum::getStatus($value['status']);
            $value['type_name']         =   LoanEnum::getType($value['type']);
            $value['reservation_at']    =   date('Y-m-d H:m:s',$value['reservation_at']);
            $value['created_at']        =   date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at']        =   date('Y-m-d H:m:s',$value['updated_at']);
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
        if (empty($data['asc'])){
            $data['asc'] = 1;
        }
        $page           = $data['page'] ?? 1;
        $asc            = $data['asc'] ==  1 ? 'asc' : 'desc';
        $page_num       = $data['page_num'] ?? 20;
        $keywords       = $data['keywords'] ?? null;
        $column         = ['*'];
        $type           = $data['type'] ?? null;
        $where          = ['id' => ['<>',0]];
        if ($type !== null){
            $where['type']  = $type;
        }
        if (!empty($keywords)){
            $keyword = [$keywords => ['name','mobile']];
            if (!$list = LoanPersonalRepository::search($keyword,$where,$column,$page,$page_num,'id',$asc)){
                $this->setError('获取失败！');
                return false;
            }
        }
        if (!$list = LoanPersonalRepository::getList($where,$column,'id',$asc,$page,$page_num)){
            $this->setError('获取失败！');
            return false;
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
        if (!$orderInfo= LoanPersonalRepository::getOne(['id' => $id,'user_id' => $memberInfo['m_id']])){
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
        if (!LoanEnum::isset($data['type'])){
              $this->setError('该推荐类型不存在');
        }
        if (!LoanEnum::isset($data['price'])){
            $this->setError('没有此价格的贷款!');
            return false;
        }
        if (!LoanEnum::isset($data['type'])){
            $this->setError('没有此价格的贷款!');
            return false;
        }
        $add_arr  = [
            'user_id'         =>  $memberInfo->m_id,
            'name'            =>  $data['name'],
            'mobile'          =>  $data['mobile'],
            'price'           =>  $data['price'],
            'ent_name'        =>  $data['ent_name'],
            'ent_title'       =>  $data['ent_title'],
            'address'         =>  $data['address'],
            'type'            =>  $data['type'],
            'remark'          =>  $data['remark'],
            'status'          =>  LoanEnum::SUBMIT,
            'reservation_at'  =>  strtotime($data['reservation_at']),
        ];
        if (!LoanPersonalRepository::exists($add_arr)){
            $this->setError('您已预约，请勿重复预约!');
            return false;
        }
        $add_arr['created_at']     =  time();
        if (!$res = LoanPersonalRepository::getAddId($add_arr)){
            $this->setError('预约失败,请重试！');
            return false;
        }
        $this->setMessage('恭喜你，预约成功');
        return true;
    }

    /**
     * 修改贷款订单信息
     * @param array $data
     * @return mixed
     */
    public function updLoan(array $data)
    {
        $id = $data['id'];
        if (!LoanPersonalRepository::exists(['id' => $id,'deleted_at' => 0])){
            $this->setError('该订单不存在!');
        }
        if (!LoanEnum::isset($data['status'])){
            $this->setError('没有此状态!');
            return false;
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
            'address'         =>  $data['address'],
            'type'            =>  $data['type'],
            'remark'          =>  $data['remark'],
            'status'          =>  $data['status'],
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
     * 软删除订单
     * @param integer $id
     * @return mixed
     */
    public function delLoan($id)
    {
        if (!$loanInfo = LoanPersonalRepository::exists(['id' => $id,'deleted_at' => 0])){
            $this->setError('该订单不存在！');
            return false;
        }
        if (!$res = LoanPersonalRepository::getUpdId(['id' => $id],['deleted_at' => time()])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功');
        return true;
    }

    /**
     * 根据ID查找贷款订单信息
     * @param array $data
     * @return bool|null
     */
    public function getLoanOrderInfo(array $data)
    {
        if (!LoanPersonalRepository::exists(['id' => $data['id']])){
            $this->setError('该订单不存在！');
            return false;
        }
        if (!$list = LoanPersonalRepository::getOne(['id' => $data['id']])){
            $this->setError('没有查到该订单信息！');
            return false;
        }
        $list['type_name']         =    empty($list['type']) ? '' : LoanEnum::getType($list['type']);
        $list['status_name']       =    empty($list['status']) ? '' : LoanEnum::getStatus($list['status']);
        $list['price_name']        =    empty($list['price']) ? '' : LoanEnum::getPrice($list['price']);
        $list['reservation_at']    =    date('Y-m-d H:m:s',$list['reservation_at']);
        $list['created_at']        =    date('Y-m-d H:m:s',$list['created_at']);
        $list['updated_at']        =    date('Y-m-d H:m:s',$list['updated_at']);
        $this->setMessage('查找成功');
        return $list;
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
        if ($member = MemberRepository::getOne(['m_id' => $comment['user_id']])){
            $member_name = $comment['name'];
            $member_name = $member_name . MemberEnum::getSex($member['m_sex']);
            #短信通知
            if (!empty($comment['mobile'])){
                $smsService = new SmsService();
                $sms_template = [
                    LoanEnum::PASS   => '尊敬的'.$member_name.'您好！您的贷款预约已通过审核,我们将在24小时内负责人联系您,请保持消息畅通，谢谢！',
                    LoanEnum::NOPASS => '尊敬的'.$member_name.'您好！您的贷款预约未通过审核,请您联系客服0000-00000再次预约，谢谢！',
                ];
                $smsService->sendContent($comment['mobile'],$sms_template[$status]);
            }
        }
        $this->setMessage('审核成功！');
        return true;
    }
}
            