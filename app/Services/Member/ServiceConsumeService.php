<?php
namespace App\Services\Member;


use App\Repositories\MemberBaseRepository;
use App\Repositories\MemberServiceConsumeRepository;
use App\Repositories\MemberServiceRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Cache;

class ServiceConsumeService extends BaseService
{

    /**
     * 添加会员服务消费记录
     * @param $request
     * @return bool
     */
    public function addServiceRecord($request)
    {
        if (!MemberBaseRepository::exists(['id' => $request['member_id']])){
            $this->setError('会员不存在！');
            return false;
        }
        if (!MemberServiceRepository::exists(['id' => $request['service_id']])){
            $this->setError('服务不存在！');
            return false;
        }
        //此处后期可能还会检查服务次数剩余
        $add_arr = [
            'user_id'       => $request['member_id'],
            'service_id'    => $request['service_id'],
            'number'        => $request['number'],
            'remark'        => $request['remark'] ?? '',
        ];
        $key = md5(json_encode($add_arr));
        if (Cache::has($key)){
            $this->setError('操作频繁！');
            return false;
        }
        Cache::put($key,'true',10);
        $add_arr['created_at'] = $add_arr['updated_at'] = time();
        if (MemberServiceConsumeRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }
}
            