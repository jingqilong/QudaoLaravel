<?php


namespace App\Repositories;


use App\Models\MessageReadModel;
use App\Repositories\Traits\RepositoryTrait;

class MessageReadRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MessageReadModel $model)
    {
        $this->model = $model;
    }

//    /**
//     * 重写
//     * @param array $where
//     * @param array $data
//     * @return mixed
//     */
//    protected function firstOrCreate(array $where, array $data){
//        $result = $this->firstOrCreate($where, $data);
//        if ($result){
//            MessageSendRepository::decrementCacheMessageCount($data['user_id'],$data['user_type']);
//        }
//        return $result;
//    }
}
            