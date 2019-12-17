<?php
namespace App\Services\Member;


use App\Enums\MemberGradeEnum;
use App\Repositories\MemberGradeDefineRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
use App\Traits\HelpTrait;

class GradeDefineService extends BaseService
{
    use HelpTrait;

    /**
     * 添加等级
     * @param $request
     * @return bool
     */
    public function addGrade($request)
    {
        if (MemberGradeDefineRepository::exists(['iden' => $request['iden']])){
            $this->setError('等级已被使用！');
            return false;
        }
        if (MemberGradeDefineRepository::exists(['title' => $request['title']])){
            $this->setError('等级标题已被使用！');
            return false;
        }
        $add_arr = [
            'iden'          => $request['iden'],
            'title'         => $request['title'],
            'description'   => $request['description'],
            'status'        => $request['status'],
            'minimum_time'  => $request['minimum_time'],
            'amount'        => $request['amount'],
            'is_buy'        => $request['is_buy'],
            'image_id'      => $request['image_id'] ?? 0,
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (MemberGradeDefineRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }

    /**
     * 删除等级
     * @param $id
     * @return bool
     */
    public function deleteGrade($id)
    {
        if (!MemberGradeDefineRepository::exists(['id' => $id])){
            $this->setError('等级不存在！');
            return false;
        }
        if (MemberGradeDefineRepository::delete(['id' => $id])){
            $this->setMessage('删除成功！');
            return true;
        }

        $this->setError('删除失败！');
        return false;
    }


    /**
     * 修改等级
     * @param $request
     * @return bool
     */
    public function editGrade($request)
    {
        if (!MemberGradeDefineRepository::exists(['id' => $request['id']])){
            $this->setError('等级不存在！');
            return false;
        }
        if (isset($request['iden']) && MemberGradeDefineRepository::exists(['iden' => $request['iden'],'id' => ['<>',$request['id']]])){
            $this->setError('等级已被使用！');
            return false;
        }
        if (isset($request['title']) && MemberGradeDefineRepository::exists(['title' => $request['title'],'id' => ['<>',$request['id']]])){
            $this->setError('等级标题已被使用！');
            return false;
        }
        $upd_arr = ['updated_at'    => time()];
        if (isset($request['iden']))        $upd_arr['iden']        = $request['iden'];
        if (isset($request['title']))       $upd_arr['title']       = $request['title'];
        if (isset($request['description'])) $upd_arr['description'] = $request['description'];
        if (isset($request['status']))      $upd_arr['status']      = $request['status'];
        if (isset($request['minimum_time']))$upd_arr['minimum_time']= $request['minimum_time'];
        if (isset($request['amount']))      $upd_arr['amount']      = $request['amount'];
        if (isset($request['is_buy']))      $upd_arr['is_buy']      = $request['is_buy'];
        if (isset($request['image_id']))    $upd_arr['image_id']    = $request['image_id'];


        if (MemberGradeDefineRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }


    /**
     * 获取等级列表
     * @param $request
     * @return bool|mixed|null
     */
    public function getGradeList($request)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $where      = ['id' => ['<>',0]];
        if (!$list = MemberGradeDefineRepository::getList($where,['*'],'id','asc',$page,$page_num)){
            $this->setError('获取失败！');
            return true;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $list['data'] = ImagesService::getListImages($list['data'],['image_id' => 'single']);
        foreach ($list['data'] as &$value){
            $value['status_title'] = MemberGradeEnum::getStatus($value['status']);
            $value['is_buy_title'] = MemberGradeEnum::getIsBuy($value['is_buy']);
            $value['created_at']   = date('Y-m-d H:i:s',$value['created_at']);
            $value['updated_at']   = date('Y-m-d H:i:s',$value['updated_at']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            