<?php
namespace App\Services\Member;


use App\Repositories\MemberServiceRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class ServiceService extends BaseService
{

    /**
     * 添加服务
     * @param $request
     * @return bool|null
     */
    public function addService($request)
    {
        $parent = [];
        if (isset($request['parent_id'])){
            if (!$parent = MemberServiceRepository::getOne(['id' => $request['parent_id']])){
                $this->setError('父级服务不存在!');
                return false;
            }
        }
        DB::beginTransaction();
        if (!$service_id = MemberServiceRepository::getAddId([
            'name'          => $request['name'],
            'desc'          => $request['desc'],
            'level'         => 1,
            'parent_id'     => 0,
            'created_at'    => time(),
            'updated_at'    => time(),
        ])){
            $this->setError('服务添加失败！');
            DB::rollBack();
            return false;
        }
        if (!empty($parent)){
            if (!MemberServiceRepository::getUpdId(
                ['id' => $service_id],
                ['path' => $parent['path'].','.$service_id, 'level' => $parent['level'] + 1, 'parent_id' => $parent['id']]
            )){
                $this->setError('服务添加失败！');
                DB::rollBack();
                return false;
            }
        }else{
            if (!MemberServiceRepository::getUpdId(
                ['id' => $service_id],
                ['path' => $service_id]
            )){
                $this->setError('服务添加失败！');
                DB::rollBack();
                return false;
            }
        }
        DB::commit();
        $this->setMessage('服务添加成功！');
        return $service_id;
    }

    /**
     * @param $service_id
     * @return mixed
     */
    public function serviceDetail($service_id)
    {
        if (!MemberServiceRepository::exists(['id' => $service_id])){
            $this->setError('服务不存在！');
            return false;
        }
        if (!$service_info = MemberServiceRepository::getDetail(['id' => $service_id])){
            $this->setError('服务获取失败！');
            return false;
        }
        $this->setMessage('服务获取成功！');
        return $service_info;
    }

    /**
     * @param $service_id
     * @return bool
     */
    public function deleteService($service_id)
    {
        if (!$service = MemberServiceRepository::getOne(['id' => $service_id])){
            $this->setError('服务不存在！');
            return false;
        }
        if (MemberServiceRepository::exists(['path' => ['like',$service['path'].',%']])){
            $this->setError('该服务下存在子服务，无法直接删除！');
            return false;
        }
        if (!MemberServiceRepository::delete(['id' => $service_id])){
            $this->setError('服务删除失败！');
            return false;
        }
        $this->setMessage('服务已成功删除！');
        return true;
    }

    /**
     * 编辑服务
     * @param $request
     * @return bool|null
     */
    public function editService($request)
    {
        if (!MemberServiceRepository::exists(['id' => $request['service_id']])){
            $this->setError('服务不存在!');
            return false;
        }
        if (!$service_id = MemberServiceRepository::getUpdId(
            ['id' => $request['service_id']],
            [
                'name'          => $request['name'],
                'desc'          => $request['desc'],
                'updated_id'    => time(),
            ])){
            $this->setError('服务修改失败！');
            return false;
        }
        $this->setMessage('服务修改成功！');
        return $service_id;
    }

    /**
     * @param array $where
     * @return array
     */
    public function serviceList($where = ['id' => ['<>',0]])
    {
        $list = MemberServiceRepository::getServiceList($where);
        $this->setMessage('列表获取成功！');
        return $list;
    }
}
            