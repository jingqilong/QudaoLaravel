<?php


namespace App\Services\Member;


use App\Enums\CommonAuditStatusEnum;
use App\Enums\MemberEnum;
use App\Enums\MemberGradeEnum;
use App\Enums\ProcessCategoryEnum;
use App\Repositories\CommonImagesRepository;
use App\Repositories\MemberBaseRepository;
use App\Repositories\MemberContactRequestRepository;
use App\Repositories\MemberGradeRepository;
use App\Repositories\MemberInfoRepository;
use App\Services\Common\ImagesService;
use App\Traits\BusinessTrait;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MemberContactServices extends BaseService
{
    use HelpTrait,BusinessTrait;
    protected $auth;

    /**
     * MemberService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }


    /**
     * 添加成员联系请求
     * @param $request
     * @return bool
     */
    public function addMemberContact($request)
    {
        $member = $this->auth->user();
        if (!MemberBaseRepository::exists(['id' => $request['contact_id']])){
            $this->setError('请核实请求的联系人是否正确!');
            return false;
        }
        $add_arr = [
            'proposer_id' => $member->id,
            'contact_id'  => $request['contact_id'],
            'needs_value' => $request['needs_value'],
        ];
        if (MemberContactRequestRepository::exists($add_arr)){
            $this->setError('您已申请过该联系人,请到预约中心查看当前进度!');
            return false;
        }
        $add_arr['status']     = MemberEnum::SUBMIT;
        $add_arr['created_at'] = time();
        $add_arr['updated_at'] = time();
        DB::beginTransaction();
        if (!$id = MemberContactRequestRepository::getAddId($add_arr)){
            $this->setError('提交申请失败!');
            DB::rollBack();
            return false;
        }
        #开启流程
        $start_process_result = $this->addNewProcessRecord($id,ProcessCategoryEnum::MEMBER_CONTACT_REQUEST);
        if (100 == $start_process_result['code']){
            $this->setError('预约失败，请稍后重试！');
            DB::rollBack();
            return false;
        }
        DB::commit();
        $this->setMessage('提交申请成功!');
        return true;
    }


    /** 编辑修改成员联系请求
     * @param $request
     * @return bool
     */
    public function editMemberContact($request)
    {
        if (!$contact_info = MemberContactRequestRepository::getOne(['id' => $request['id']])){
            $this->setError('预约请求不存在!');
            return false;
        }
        if ($contact_info['status'] > MemberEnum::SUBMIT){
            $this->setError('您的请求已被审核,不能被修改!');
            return false;
        }
        if (!MemberBaseRepository::exists(['id' => $request['contact_id']])){
            $this->setError('请核实请求的联系人是否正确!');
            return false;
        }
        $upd_arr = [
            'contact_id'  => $request['contact_id'],
            'needs_value' => $request['needs_value'],
        ];
        if (MemberContactRequestRepository::exists($upd_arr)){
            $this->setError('您已申请过该联系人,请到预约中心查看当前进度!');
            return false;
        }
        $add_arr['status']     = MemberEnum::ACTIVITEMEMBER;
        $add_arr['created_at'] = time();
        $add_arr['updated_at'] = time();
        if (!MemberContactRequestRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setError('提交申请失败!');
            return false;
        }
        $this->setMessage('提交申请成功!');
        return true;
    }

    /**
     * 删除成员联系请求
     * @param $request
     * @return bool
     */
    public function delMemberContact($request)
    {
        if (!MemberContactRequestRepository::exists(['id' => $request['id']])){
            $this->setError('您的请求查看成员不存在或已被删除!');
            return false;
        }
        if (!MemberContactRequestRepository::delete(['id' => $request['id']])){
            $this->setError('删除失败!');
            return false;
        }
        $this->setMessage('删除成功!');
        return true;
    }

    /**
     * 获取成员查看成员的联系列表
     * @return bool|null
     */
    public function getMemberContact()
    {
        $member = $this->auth->user();
        $column = ['id','proposer_id','contact_id','needs_value','status','created_at'];
        if (!$list = MemberContactRequestRepository::getList(['proposer_id' => $member->id],$column)){
            $this->setError('获取失败!');
            return false;
        }
        $contact_ids  = array_column($list,'contact_id');
        $contact_list = MemberBaseRepository::getAssignList($contact_ids,['id','ch_name','avatar_id']);
        $grade_list   = MemberGradeRepository::getAssignList($contact_ids,['user_id','grade'],'user_id');
        $info_list    = MemberInfoRepository::getAssignList($contact_ids,['member_id','employer'],'member_id');
        $contact_list = ImagesService::getListImagesConcise($contact_list,['avatar_id' => 'single']);
        $base_arr = [];
        foreach ($list as &$value){
            if ($contacts = $this->searchArray($contact_list,'id',$value['contact_id'])){
                $base_arr = reset($contacts);
            }
            if ($grades = $this->searchArray($grade_list,'user_id',$value['contact_id'])){
                $value['grades'] = reset($grades)['grade'];
            }
            if ($info = $this->searchArray($info_list,'member_id',$value['contact_id'])){
                $value['employer'] = reset($info)['employer'];
            }
            $value = array_merge($base_arr,$value);
            $value['status_name'] = MemberEnum::getAuditStatus($value['status'],'待审核');
            $value['grades_name'] = MemberEnum::getGrade($value['grades'],'普通成员');
        }
        $this->setMessage('获取成功!');
        return $list;
    }

    /**
     * OA 获取成员查看成员的联系列表
     * @param $request
     * @return bool|null
     */
    public function getMemberContactList($request)
    {
        $employee = Auth::guard('oa_api')->user();
        $status    = $request['status'] ?? null;
        $page      = $request['page'] ?? 1;
        $page_num  = $request['page_num'] ?? 20;
        $where  = ['id' => ['<>',0]];
        $column = ['id','proposer_id','contact_id','needs_value','status','created_at'];
        if (!is_null($status)) $where['status'] = $status;
        if (!$list = MemberContactRequestRepository::getList($where,$column,'id','asc',$page,$page_num)){
            $this->setError('获取失败!');
            return false;
        }
        $list = $this->removePagingField($list);
        $list['data'] = MemberGradeRepository::bulkHasOneWalk($list['data'], ['from' => 'proposer_id','to' => 'user_id'], ['user_id','grade'], [],
            function ($src_item,$member_grade_items){
                $src_item['proposer_grade']       = $member_grade_items['grade'] ?? '';
                $src_item['proposer_grade_name']  = MemberEnum::getGrade($member_grade_items['grade'],'普通成员');
                return $src_item;
            });
        $list['data'] = MemberGradeRepository::bulkHasOneWalk($list['data'], ['from' => 'contact_id','to' => 'user_id'], ['user_id','grade'], [],
            function ($src_item,$member_grade_items){
                $src_item['contact_grade']       = $member_grade_items['grade'] ?? '';
                $src_item['contact_grade_name']  = MemberEnum::getGrade($member_grade_items['grade'],'普通成员');
                return $src_item;
            });
        $list['data'] = MemberBaseRepository::bulkHasOneWalk($list['data'], ['from' => 'proposer_id','to' => 'id'], ['id','ch_name'], [],
            function ($src_item,$member_grade_items){
                $src_item['proposer_grade']       = $member_grade_items['ch_name'] ?? '';
                return $src_item;
            });
        $list['data'] = MemberBaseRepository::bulkHasOneWalk($list['data'], ['from' => 'contact_id','to' => 'id'], ['id','ch_name'], [],
            function ($src_item,$member_grade_items)use($employee){
                $src_item['contact_grade']       = $member_grade_items['ch_name'] ?? '';
                $src_item['status_name']         = MemberEnum::getAuditStatus($src_item['status']);
                #获取流程信息
                $src_item['progress']           = $this->getBusinessProgress($src_item['id'],ProcessCategoryEnum::MEMBER_CONTACT_REQUEST,$employee->id);
                return $src_item;
            });
        $list['data'] = array_values($list['data']);
        $this->setMessage('获取成功!');
        return $list;
    }

    /**
     * 获取成员联系申请详情
     * @param $id
     * @return array|bool
     */
    public function getMemberContactDetail($id){
        $employee = Auth::guard('oa_api')->user();
        if (!$contact_info = MemberContactRequestRepository::getOne(['id' => $id])){
            $this->setError('申请不存在！');
            return false;
        }
        $member_ids         = [$contact_info['proposer_id'],$contact_info['contact_id']];
        $member_base_list   = MemberBaseRepository::getAssignList($member_ids,['id','ch_name']);
        $member_base_list   = MemberBaseRepository::createArrayIndex($member_base_list,'id');
        $grade_where        = ['user_id' => ['in',$member_ids],'status' => MemberGradeEnum::PASS,'end_at' => ['range',['-1',time()]]];
        $member_grade_list  = MemberGradeRepository::getList($grade_where,['user_id','grade']);
        $member_grade_list  = MemberGradeRepository::createArrayIndex($member_grade_list,'user_id');
        $detail = [
            'id'            => $contact_info['id'],
            'proposer_name' => $member_base_list[$contact_info['proposer_id']]['ch_name'] ?? '',
            'proposer_grade'=> MemberEnum::getGrade($member_grade_list[$contact_info['proposer_id']]['grade'] ?? 0,'普通成员'),
            'contact_name'  => $member_base_list[$contact_info['contact_id']]['ch_name'] ?? '',
            'contact_grade' => MemberEnum::getGrade($member_grade_list[$contact_info['contact_id']]['grade'] ?? 0,'普通成员'),
            'needs_value'   => $contact_info['needs_value'],
            'status'        => CommonAuditStatusEnum::getAuditStatus($contact_info['status']),
            'created_at'    => empty($contact_info['created_at']) ? date('Y-m-d H:i:s',$contact_info['created_at']) : '',
            'updated_at'    => empty($contact_info['updated_at']) ? date('Y-m-d H:i:s',$contact_info['updated_at']) : '',
        ];
        return $this->getBusinessDetailsProcess($detail,ProcessCategoryEnum::MEMBER_CONTACT_REQUEST,$employee->id);
    }

    /**
     * OA 审核成员联系成员预约
     * @param $id
     * @param $audit
     * @return bool
     */
    public function setMemberContact($id, $audit)
    {
        if (!$contact_info = MemberContactRequestRepository::getOne(['id' => $id])){
            $this->setError('没有预约!');
            return false;
        }
        if ($contact_info['status'] > MemberEnum::SUBMIT){
            $this->setError('审核类型已被审核');
            return false;
        }
        if (!MemberContactRequestRepository::getUpdId(['id' => $id],['status' => $audit])){
            $this->setError('审核失败!');
            return false;
        }
        $this->setMessage('审核成功!');
        return true;
    }

    /**
     * 获取联系成员详情
     * @param $id
     * @return bool|null
     */
    public function getMemberContactInfo($id)
    {
        if (!$contact_info = MemberContactRequestRepository::getOne(['id' => $id])){
            $this->setError('获取失败!');
            return false;
        }
        $contact_info['contact_name']  = MemberBaseRepository::getField(['id' => $contact_info['contact_id']],'ch_name');
        $proposer_base = MemberBaseRepository::getOne(['id' => $contact_info['proposer_id']],['avatar_id','ch_name']);
        $contact_info['proposer_name']  = $proposer_base['ch_name'];
        $contact_info['proposer_url']   = CommonImagesRepository::getField(['id' => $proposer_base['avatar_id']],'img_url');
        $contact_info['employer']       = MemberInfoRepository::getField(['member_id' => $id],'employer');
        $contact_info['status_name']    = MemberEnum::getAuditStatus($contact_info['status'],'待审核');
        unset($contact_info['proposer_id'],$contact_info['updated_at']);
        $this->setMessage('获取成功!');
        return $contact_info;
    }
}