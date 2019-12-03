<?php
namespace App\Services\Activity;


use App\Repositories\ActivityDetailRepository;
use App\Repositories\ActivityHostsRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class HostsService extends BaseService
{

    /**
     * 给活动添加举办方
     * @param $request
     * @return bool
     */
    public function addHost($request)
    {
        if (!ActivityDetailRepository::exists(['id' => $request['activity_id']])){
            $this->setError('活动不存在！');
            return false;
        }
        $parameter = json_decode($request['parameters'],true);
        $type = [1,2];
        $add_arr = [];
        foreach ($parameter as $value){
            if (!isset($value['type'])){
                $this->setError('举办方类别不能为空！');
                return false;
            }
            if (!isset($value['name'])){
                $this->setError('举办方名称不能为空！');
                return false;
            }
            if (!isset($value['logo_id'])){
                $this->setError('举办方logo图不能为空！');
                return false;
            }
            if (!in_array($value['type'],$type)){
                $this->setError('举办方类别不存在！');
                return false;
            }
            if (!is_integer($value['logo_id'])){
                $this->setError('举办方logo图ID必须为整数！');
                return false;
            }
            $add_arr[] = [
                'activity_id'   => $request['activity_id'],
                'type'          => $value['type'],
                'name'          => $value['name'],
                'logo_id'       => $value['logo_id'],
                'created_at'    => time(),
                'updated_at'    => time(),
            ];
        }
        DB::beginTransaction();
        ActivityHostsRepository::delete(['activity_id'   => $request['activity_id']]);
        if (ActivityHostsRepository::exists(['activity_id'   => $request['activity_id']])){
            $this->setError('添加失败！');
            DB::rollBack();
            return false;
        }
        if (!ActivityHostsRepository::create($add_arr)){
            $this->setError('添加失败！');
            DB::rollBack();
            return false;
        }
        $this->setMessage('添加成功！');
        DB::commit();
        return true;
    }
}
            