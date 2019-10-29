<?php
namespace App\Services\Oa;


use App\Repositories\OaAdminOperationLogRepository;
use App\Repositories\OaEmployeeRepository;
use App\Services\BaseService;

class AdminOperationLogService extends BaseService
{

    /**
     * 获取操作日志
     * @param $page
     * @param $pageNum
     * @return bool|null
     */
    public function getOperationLog($page, $pageNum)
    {
        if (!$list = OaAdminOperationLogRepository::getList(['id' => ['>',0]],['*'],'id','desc',$page,$pageNum)){
            $this->setError('获取失败!');
            return false;
        }
        unset($list['first_page_url'], $list['from'], $list['from'], $list['last_page_url'], $list['next_page_url'], $list['path'], $list['prev_page_url'], $list['to']);
        if (empty($list['data'])){
            $this->setMessage('暂无数据!');
            return $list;
        }
        foreach ($list['data'] as &$value){
            $value['user_name'] = OaEmployeeRepository::getField(['id' => $value['user_id']],'real_name');
            unset($value['updated_at'],$value['user_id']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            