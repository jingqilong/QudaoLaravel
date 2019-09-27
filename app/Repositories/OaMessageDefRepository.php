<?php


namespace App\Repositories;


use App\Models\OaMessageDefModel;
use App\Repositories\Traits\RepositoryTrait;

class OaMessageDefRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaMessageDefModel $model)
    {
        $this->model = $model;
    }

    /**
     * 添加消息
     * @param $data
     * @return integer|null
     */
    protected function addMessage($data){
        $add = [
            'title'      => $data['title'],
            'type'       => $data['type'],
            'content'    => $data['title'],
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s'),
        ];
        return $this->getAddId($add);
    }
}
            