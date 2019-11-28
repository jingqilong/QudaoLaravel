<?php
namespace App\Services\Message;


use App\Repositories\MessageDefRepository;
use App\Services\BaseService;

class DefService extends BaseService
{

    /**
     * 添加消息并返回消息ID
     * @param $category
     * @param $title
     * @param $content
     * @param null $relate_id
     * @param null $image_ids
     * @param null $url
     * @return bool|null
     */
    protected function addMessage($category, $title, $content, $relate_id = null, $image_ids = null, $url = null){
        $add_arr = [
            'category'  => $category,
            'title'     => $title,
            'content'   => $content,
            'relate_id' => $relate_id,
            'image_ids' => $image_ids,
            'url'       => $url,
            'created_at'=> date('Y-m-d H:i:s'),
            'updated_at'=> date('Y-m-d H:i:s'),
        ];
        if ($message_id = MessageDefRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return $message_id;
        }
        $this->setError('添加失败！');
        return false;
    }
}
            