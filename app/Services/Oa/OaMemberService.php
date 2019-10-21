<?php


namespace App\Services\Oa;


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
        $where          = ['m_starte' => ['in',[0,1,2]]];
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
        if (!$memberInfo['m_starte']  == '9'){
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
        if (!$memberInfo = OaMemberRepository::getUpdId(['m_id' => $id],['m_starte' => $status])){
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
        if ($memberInfo['m_starte'] == 9){
            $this->setError('成员已被删除!');
            return false;
        }
        if ($memberInfo['m_starte'] == 0){
            if (!$memberStatus = OaMemberRepository::getUpdId(['m_id' => $id],['m_starte' => 1])){
                $this->setError('禁用成员失败!');
                return $memberStatus;
            }
            $this->setMessage('禁用会员成功!');
            return $memberStatus;
        }else{
            if (!$memberStatus = OaMemberRepository::getUpdId(['m_id' => $id],['m_starte' => 0])){
                $this->setError('激活会员失败!');
                return $memberStatus;
            }
            $this->setMessage('激活会员成功!');
            return $memberStatus;
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
            $this->setError('没有数据，请重新添加');
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

}