<?php
namespace App\Services\Member;


use App\Repositories\MemberGradeServiceRepository;
use App\Repositories\MemberServiceRepository;
use App\Services\BaseService;

class GradeServiceService extends BaseService
{

    /**
     * 给等级添加服务
     * @param $request
     * @return bool|null
     */
    public function gradeAddService($request)
    {
        if (!$service = MemberServiceRepository::getOne(['id' => $request['service_id']])){
            $this->setError('服务不存在!');
            return false;
        }
        if (MemberGradeServiceRepository::exists(
            ['grade'         => $request['grade'],
            'service_id'    => $request['service_id'],])){
            $this->setError('此等级该服务已存在，请勿重复添加!');
            return false;
        }
        if (!MemberGradeServiceRepository::create([
            'grade'         => $request['grade'],
            'service_id'    => $request['service_id'],
            'status'        => $request['status'],
            'number'        => $request['number'],
            'cycle'         => $request['cycle'] * 86400,
            'created_at'    => time(),
            'updated_at'    => time(),
        ])){
            $this->setError('给等级添加服务失败!');
            return false;
        }
        $this->setMessage('给等级添加服务成功！');
        return true;
    }

    /**
     * 删除等级中的服务
     * @param $id
     * @return bool
     */
    public function gradeDeleteService($id)
    {
        if (!MemberGradeServiceRepository::exists(['id' => $id])){
            $this->setError('记录不存在!');
            return false;
        }
        if (!MemberGradeServiceRepository::delete(['id' => $id])){
            $this->setError('服务删除失败!');
            return false;
        }
        $this->setMessage('服务删除成功！');
        return true;
    }

    /**
     * 修改
     * @param $request
     * @return bool
     */
    public function gradeEditService($request)
    {
        if (!$service = MemberServiceRepository::getOne(['id' => $request['service_id']])){
            $this->setError('服务不存在!');
            return false;
        }
        if (!MemberGradeServiceRepository::exists(['id' => $request['id']])){
            $this->setError('此条记录不存在!');
            return false;
        }
        if (!MemberGradeServiceRepository::getUpdId(
            ['id'           => $request['id']],
            [
            'grade'         => $request['grade'],
            'service_id'    => $request['service_id'],
            'status'        => $request['status'],
            'number'        => $request['number'],
            'cycle'         => $request['cycle'] * 86400,
            'updated_at'    => time(),
        ])){
            $this->setError('修改失败!');
            return false;
        }
        $this->setMessage('修改成功！');
        return true;
    }
}
            