<?php


namespace App\Services\Oa;


use App\Enums\MemberEnum;
use App\Repositories\MemberGradeViewRepository;
use App\Repositories\OaMemberRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;

class OaMemberService extends BaseService
{
    use HelpTrait;
    protected $auth;

    /**
     * OaMemberService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('oa_api');
    }


    /**
     * 获取成员列表
     * @param array $data
     * @return mixed
     */
    public function getMemberList(array $data)
    {
        if (empty($data['asc'])){
            $data['asc'] = 1;
        }
        $page           = $data['page'] ?? 1;
        $page_num       = $data['page_num'] ?? 20;
        $asc            = $data['asc'] ==  1 ? 'asc' : 'desc';
        $keywords       = $data['keywords'] ?? null;
        $column         = ['id','card_no','ch_name','en_name','sex','mobile','grade','position','address','employer','img_url','title','category','status','hidden','created_at'];
        $where          = ['deleted_at' => 0];
        if (!empty($keywords)){
            $keyword        = [$keywords => ['ch_name','en_name','category','card_no','mobile','grade']];
            if(!$member_list = MemberGradeViewRepository::search($keyword,$where,$column,$page,$page_num,'created_at',$asc)){
                $this->setMessage('暂无成员信息！');
                return [];
            }
        }else {
            if(!$member_list = MemberGradeViewRepository::getList($where,$column,'created_at',$asc,$page,$page_num)){
                $this->setMessage('没有查到该成员！');
                return [];
            }
        }
        $this->removePagingField($member_list);
        if (empty($member_list['data'])) {
            $this->setMessage('没有成员!');
        }
        foreach ($member_list['data'] as &$value){
            $value['grade']       = MemberEnum::getGrade($value['grade'],'普通成员');
            $value['category']    = MemberEnum::getCategory($value['category'],'普通成员');
            $value['sex_name']    = MemberEnum::getSex($value['sex'],'未设置');
            $value['status_name'] = MemberEnum::getStatus($value['status'],'成员');
            $value['hidden']      = MemberEnum::getHidden($value['hidden'],'显示');
            $value['created_at']  = date('Y-m-d H:i:s',$value['created_at']);
        }
        $this->setMessage('获取成功！');
        return $member_list;
    }

    /**
     * 获取成员信息
     * @param string $id
     * @return bool|null
     */
    public function getMemberInfo(string $id)
    {
        if (!$member = OaMemberRepository::exists(['m_id' => $id])){
            $this->setError('用户信息不存在!');
            return false;
        }
        if (!$memberInfo = OaMemberRepository::getOne(['m_id' => $id])){
            $this->setError('用户信息获取失败!');
            return false;
        }
        if (!$memberInfo['deleted_at']  != 0){
            $this->setError('用户已被删除，有需求请联系超级管理员!');
            return false;
        }
        if (empty($memberInfo)) {
            $this->setMessage('没有查找到此成员信息!');
        }
        $memberInfo['group_name']        = MemberEnum::getGrade($memberInfo['m_groupname'],$memberInfo['m_groupname']);
        $memberInfo['category_name']     = MemberEnum::getCategory($memberInfo['m_category'],$memberInfo['m_category']);
        $memberInfo['starte']            = MemberEnum::getStatus($memberInfo['m_starte'],$memberInfo['m_starte']);
        $memberInfo['sex']               = MemberEnum::getSex($memberInfo['m_sex'],$memberInfo['m_sex']);

        $this->setMessage('获取用户信息成功');
        return $memberInfo;
    }

    /**
     * 成员软删除
     * @param string $id
     * @return bool|null
     */
    public function delMember(string $id)
    {
        if (empty($id)){
            $this->setError('会员ID为空！');
            return false;
        }
        if (!OaMemberRepository::exists(['m_id' => $id])){
            $this->setError('用户信息不存在!');
            return false;
        }
        if (!$memberInfo = OaMemberRepository::getOne(['m_id' => $id])){
            $this->setError('没有该成员!');
            return false;
        }
        if ($memberInfo['deleted_at']  != 0){
            $this->setError('用户已被删除!');
            return false;
        }
        if (!$memberInfo = OaMemberRepository::getUpdId(['m_id' => $id],['deleted_at' => time()])){
            $this->setError('删除成员失败!');
            return false;
        }
        $this->setMessage('删除成员成功');
        return $memberInfo;
    }

    /**
     * 成员禁用or激活
     * @param string $id
     * @return bool|null
     */
    public function setMemberStatus(string $id)
    {
        if (empty($id)){
            $this->setError('会员ID为空！');
            return false;
        }
        if (!$memberID = OaMemberRepository::exists(['m_id' => $id])){
            $this->setError('用户信息不存在!');
            return false;
        }
        if (!$memberInfo = OaMemberRepository::getOne(['m_id' => $id])){
            $this->setError('用户信息获取失败!');
            return false;
        }

        switch ($memberInfo['m_starte'])
        {
            case '0':
                if (!$memberStatus = OaMemberRepository::getUpdId(['m_id' => $id],['m_starte' => MemberEnum::DISABLEMEMBER])){
                    $this->setError('禁用成员失败!');
                    return false;
                }
                $this->setMessage('禁用会员成功!');
                return $memberStatus;
                break;
            case '1':
                if (!$memberStatus = OaMemberRepository::getUpdId(['m_id' => $id],['m_starte' => MemberEnum::ACTIVITEMEMBER])){
                    $this->setError('激活会员失败!');
                    return false;
                }
                $this->setMessage('激活会员成功!');
                return $memberStatus;
                break;
            case '2':
                if (!$memberStatus = OaMemberRepository::getUpdId(['m_id' => $id],['m_starte' => MemberEnum::DISABLEOFFICER])){
                    $this->setError('禁用官员失败!');
                    return false;
                }
                $this->setMessage('禁用官员成功!');
                return $memberStatus;
                break;
            case '3':
                if (!$memberStatus = OaMemberRepository::getUpdId(['m_id' => $id],['m_starte' => MemberEnum::ACTIVITEOFFICER])){
                    $this->setError('激活官员失败!');
                    return false;
                }
                $this->setMessage('激活官员成功!');
                return $memberStatus;
                break;
                default;
        }
    }


    /**
     * 添加成员
     * @param $data
     * @return bool|null
     */
    public function addMember($data)
    {
        unset($data['sign'],$data['token']);
        if (empty($data)){
            $this->setError('没有数据，请添加数据');
            return false;
        }

        if (empty($data['m_starte'])){
            $data['m_starte'] = MemberEnum::DISABLEMEMBER;
        }

        if ($old_member = OaMemberRepository::exists(['m_cname' => $data['m_cname'],'m_phone' =>$data['m_phone'],'m_ename' =>$data['m_ename'],'m_email' => $data['m_email']])){
            $this->setError('会员已存在，请不要重新添加');
            return false;
        }

        if (!$memberInfo = OaMemberRepository::getAddId($data)){
            $this->setError('添加失败，请重新添加！');
            return false;
        }

        $this->setMessage('添加成功');
        return $memberInfo;

    }


    /**
     * 更新完善成员信息
     * @param $data
     * @return bool|null
     */
    public function updMemberInfo($data)
    {
        unset($data['sign'],$data['token']);
        if (empty($data)){
            $this->setError('没有数据，请编辑修改数据');
            return false;
        }

        if (!$old_member = OaMemberRepository::getOne(['m_id' => $data['id']])){
            $this->setError('查找不到该会员,请重试！');
            return false;
        }

        $table_fields = OaMemberRepository::getFields();
        $upd_data = [];
        foreach($table_fields as $v){
            if (isset($data[$v]) && $old_member[$v] !== $data[$v]){
                $upd_data[$v] = $data[$v];
            }
        }

        if (!$updMemberInfo = OaMemberRepository::getUpdId(['m_id' => $data['id']],$upd_data)){
            $this->setError('更新失败！请重试');
            return false;
        }
        $this->setMessage('更新成功');
        return $updMemberInfo;


    }

}