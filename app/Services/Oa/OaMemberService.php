<?php


namespace App\Services\Oa;


use App\Enums\MemberEnum;
use App\Repositories\OaMemberRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;

class OaMemberService extends BaseService
{
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
     * @return array|bool|null
     */
    public function getMemberList()
    {
        $page           = $data['page'] ?? 1;
        $page_num       = $data['page_num'] ?? 20;
        $keywords       = $data['keywords'] ?? null;
        $column         = ['field' => '*'];
        $where          = ['m_starte' => ['in',[MemberEnum::ACTIVITEMEMBER,MemberEnum::DISABLEMEMBER,MemberEnum::ACTIVITEOFFICER,MemberEnum::DISABLEOFFICER]]];
        $keyword        = [$keywords => ['m_cname','m_ename','m_category','m_num','m_phone','m_groupname']];

        if(!$member_list = OaMemberRepository::search($keyword,$where,$column,$page,$page_num,'m_time','desc')){
            $this->setMessage('没有查到该成员！');
            return [];
        }

        unset($member_list['first_page_url'], $member_list['from'],
              $member_list['last_page_url'],  $member_list['from'],
              $member_list['next_page_url'],  $member_list['path'],
              $member_list['prev_page_url'],  $member_list['to']);

        if (empty($member_list['data'])) {
            $this->setMessage('没有成员!');
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
        if (!empty($id)){
            $this->setError('会员ID为空！');
            return false;
        }
        if (!$memberInfo = OaMemberRepository::exists(['m_id' => $id])){
            $this->setError('用户信息不存在!');
            return false;
        }
        if (!$memberInfo = OaMemberRepository::getOne(['m_id' => $id])){
            $this->setError('用户信息获取失败!');
            return false;
        }
        if (!$memberInfo['m_starte']  == MemberEnum::DELETEMEMBER){
            $this->setError('用户已被删除，有需求请联系超级管理员!');
            return false;
        }
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
        $status = '9';  //成员软删除
        if (empty($id)){
            $this->setError('会员ID为空！');
            return false;
        }
        if (!$memberInfo = OaMemberRepository::exists(['m_id' => $id])){
            $this->setError('用户信息不存在!');
            return false;
        }
        if (!$memberInfo = OaMemberRepository::getOne(['m_id' => $id])){
            $this->setError('用户信息获取失败!');
            return false;
        }
        if (!$memberInfo = OaMemberRepository::getUpdId(['m_id' => $id],['m_starte' => MemberEnum::DELETEMEMBER])){
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
        if ($memberInfo['m_starte'] == MemberEnum::DELETEMEMBER){
            $this->setError('成员已被删除!');
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
        $data['m_sex'] = $data['m_sex'] == '1' ?  '先生' : '女士';
        if (empty($data['m_starte'])){
            $data['m_starte'] = MemberEnum::DISABLEMEMBER;
        }
        switch ($data['m_groupname'])
        {
            case 1:
                $data['m_groupname'] = '内部测试';
            break;
            case 2:
                $data['m_groupname'] = '亦享成员';
            break;
            case 3:
                $data['m_groupname'] = '至享成员';
            break;
            case 4:
                $data['m_groupname'] = '悦享成员';
            break;
            case 5:
                $data['m_groupname'] = '真享成员';
            break;
            case 6:
                $data['m_groupname'] = '君享成员';
            break;
            case 7:
                $data['m_groupname'] = '尊享成员';
            break;
            case 8:
                $data['m_groupname'] = '内部测试';
            break;
            default;
        }
        if (empty($data)){
            $this->setError('没有数据，请添加数据');
            return false;
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
            $this->setError('查找不到该会员！');
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