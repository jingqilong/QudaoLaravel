<?php
namespace App\Services\Oa;


use App\Enums\ProcessEventEnum;
use App\Repositories\OaProcessActionRelatedRepository;
use App\Repositories\OaProcessEventsRepository;
use App\Services\BaseService;

class ProcessEventsService extends BaseService
{

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
            'execute'       => $request['execute'],
            'status'        => ProcessEventEnum::getConst($request['status']),
            'description'   => $request['description'],
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
        if (OaProcessActionRelatedRepository::exists(['event_ids' => ['like','%,'.$event_id . ',%']]) ||
            OaProcessActionRelatedRepository::exists(['event_ids' => ['like',$event_id . ',%']])
        ){
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
            'execute'       => $request['execute'],
            'status'        => ProcessEventEnum::getConst($request['status']),
            'description'   => $request['description'],
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
        unset($event_list['first_page_url'], $event_list['from'],
            $event_list['from'], $event_list['last_page_url'],
            $event_list['next_page_url'], $event_list['path'],
            $event_list['prev_page_url'], $event_list['to']);
        if (empty($event_list['data'])){
            $this->setMessage('暂无数据!');
            return $event_list;
        }
        foreach ($event_list['data'] as &$value){
            $value['status_label']  = ProcessEventEnum::getStatus($value['status']);
            $value['status']        = ProcessEventEnum::$status[$value['status']];
            $value['created_at']    = date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at']    = date('Y-m-d H:m:s',$value['updated_at']);
        }
        $this->setMessage('获取成功！');
        return $event_list;
    }
}
            