<?php


namespace App\Services\Oa;


use App\Services\BaseService;
use App\Traits\BusinessTrait;

class BusinessService extends BaseService
{
    use BusinessTrait;

    /**
     * 获取业务流程进度
     * @param $request
     * @return bool
     */
    public function getBusinessProcessProgress($request)
    {
        $result = $this->getProcessRecordList($request);
        if (100 == $result['code']){
            $this->setError($result['message']);
            return false;
        }
        $this->setMessage('获取成功！');
        return $result['data'];
    }
}