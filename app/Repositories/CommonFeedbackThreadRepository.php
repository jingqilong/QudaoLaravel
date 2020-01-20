<?php


namespace App\Repositories;


use App\Enums\FeedBacksEnum;
use App\Models\CommonFeedbackThreadModel;
use App\Repositories\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Cache;

class CommonFeedbackThreadRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(CommonFeedbackThreadModel $model)
    {
        $this->model = $model;
    }

    /**
     * 重写添加数据方法，
     * @param $data
     * @return null
     */
    protected function getAddId(array $data=[]){
        if ($id=$this->model->insertGetId($data)){
            $this->cacheData($id,$data);
        }
        return $id>0 ? $id : null;
    }

    /**
     * 缓存消息
     * @param $id
     * @param $data
     */
    private function cacheData($id, $data){
        //将新增的消息写入缓存
        $feed_back_key  = config('message.feed_back_key','feed_back_key');
        $key            = base64UrlEncode($feed_back_key.'.'.$data['feedback_id']);
        if (!Cache::has($key)){
            Cache::put($key,$id,null);
        }
    }
}
            