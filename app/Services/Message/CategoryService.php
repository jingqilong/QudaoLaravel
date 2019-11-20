<?php
namespace App\Services\Message;


use App\Enums\MessageEnum;
use App\Enums\MessageTemplateEnum;
use App\Repositories\MessageCategoryRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;

class CategoryService extends BaseService
{
    use HelpTrait;

    /**
     * 添加类别
     * @param $request
     * @return bool
     */
    public function addCategory($request)
    {
        if (MessageCategoryRepository::exists(['title' => $request['title']])){
            $this->setError('消息标签已被使用！');
            return false;
        }
        $add_arr = [
            'title'     => $request['title'],
            'explain'   => $request['explain'],
            'status'    => $request['status'],
            'created_at'=> date('Y-m-d H:i:s'),
            'updated_at'=> date('Y-m-d H:i:s'),
        ];
        if (MessageCategoryRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }

    /**
     * 禁用或开启类别
     * @param $id
     * @return bool
     */
    public function disableCategory($id)
    {
        $where = ['id' => $id];
        if (!$category = MessageCategoryRepository::exists($where)){
            $this->setError('消息类别不存在！');
            return false;
        }
        if ($category['status'] == MessageEnum::OPEN){
            if (MessageCategoryRepository::getUpdId($where,['status' => MessageEnum::DISABLE,'updated_at' => date('Y-m-d H:i:s')])){
                $this->setMessage('禁用成功！');
                return true;
            }
        }else{
            if (MessageCategoryRepository::getUpdId($where,['status' => MessageEnum::OPEN,'updated_at' => date('Y-m-d H:i:s')])){
                $this->setMessage('开启成功！');
                return true;
            }
        }
        $this->setError('操作失败！');
        return false;
    }

    /**
     * 编辑类别
     * @param $request
     * @return bool
     */
    public function editCategory($request)
    {
        if (MessageCategoryRepository::exists(['id' => $request['id']])){
            $this->setError('消息类别不存在！');
            return false;
        }
        if (MessageCategoryRepository::exists(['title' => $request['title'],'id' => ['<>',$request['id']]])){
            $this->setError('消息标签已被使用！');
            return false;
        }
        $add_arr = [
            'title'     => $request['title'],
            'explain'   => $request['explain'],
            'status'    => $request['status'],
            'updated_at'=> date('Y-m-d H:i:s'),
        ];
        if (MessageCategoryRepository::getUpdId(['id' => $request['id']],$add_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取类别列表
     * @param $request
     * @return bool|mixed|null
     */
    public function getCategoryList($request){
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $status     = $request['status'] ?? null;
        $where      = ['id' => ['<>',0]];
        if (!is_null($status)){
            $where['status'] = $status;
        }
        if (!$list = MessageCategoryRepository::getList($where,['*'],'id','asc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        foreach ($list['data'] as &$value){
            $value['status_title'] = MessageEnum::getCategoryStatus($value['status']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

}
            