<?php
namespace App\Services\Oa;


use App\Enums\ProcessEventEnum;
use App\Enums\ProcessEventStatusEnum;
use App\Repositories\OaProcessActionEventRepository;
use App\Repositories\OaProcessEventsRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;

/**
 * @desc 基础数据：定义事件 codeBy: bardo
 * Class ProcessEventsService
 * @package App\Services\Oa
 *
 */
class ProcessEventsService extends BaseService
{
    use HelpTrait;

    /**
     * 添加事件
     * @param $request
     * @return bool
     */
    public function addEvent($request)
    {
        if (OaProcessEventsRepository::exists(['name' => $request['name']])){
            $this->setError('名称已被使用！');
            return false;
        }
        $add_arr = [
            'name'          => $request['name'],
            'event_type'    => $request['event_type'],
            'status'        => $request['status'],
            'description'   => $request['description'] ?? '',
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (OaProcessEventsRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }

    /**
     * 删除事件
     * @param $event_id
     * @return bool
     */
    public function deleteEvent($event_id)
    {
        if (!OaProcessEventsRepository::exists(['id' => $event_id])){
            $this->setError('该事件不存在！');
            return false;
        }
        if (OaProcessActionEventRepository::exists(['event_id' => $event_id])){
            $this->setError('无法删除正在使用的事件！');
            return false;
        }
        if (!OaProcessEventsRepository::delete(['id' => $event_id])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功！');
        return true;
    }

    /**
     * 修改事件
     * @param $request
     * @return bool
     */
    public function editEvent($request)
    {
        if (!$event = OaProcessEventsRepository::getOne(['id' => $request['event_id']])){
            $this->setError('该事件不存在！');
            return false;
        }
        if ($event['name'] != $request['name'] && OaProcessEventsRepository::exists(['name' => $request['name']])){
            $this->setError('名称已被使用！');
            return false;
        }
        $upd_arr = [
            'name'          => $request['name'],
            'event_type'    => $request['event_type'],
            'status'        => $request['status'],
            'description'   => $request['description'] ?? '',
            'updated_at'    => time(),
        ];
        if (OaProcessEventsRepository::getUpdId(['id' => $request['event_id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取事件列表
     * @param $page
     * @param $pageNum
     * @return bool|null
     */
    public function getEventList($page, $pageNum)
    {
        if (!$event_list = OaProcessEventsRepository::getList(['id' => ['>',0]],['*'],'id','asc',$page,$pageNum)){
            $this->setError('获取失败!');
            return false;
        }
        $event_list = $this->removePagingField($event_list);
        if (empty($event_list['data'])){
            $this->setMessage('暂无数据!');
            return $event_list;
        }
        foreach ($event_list['data'] as &$value){
            $value['event_type_title'] = ProcessEventEnum::getLabelByValue($value['event_type']);
            $value['status_title']  = ProcessEventStatusEnum::getLabelByValue($value['status']);
            $value['created_at']    = date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at']    = date('Y-m-d H:m:s',$value['updated_at']);
        }
        $this->setMessage('获取成功！');
        return $event_list;
    }
}
            