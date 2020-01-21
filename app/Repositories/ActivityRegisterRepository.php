<?php


namespace App\Repositories;


use App\Enums\ActivityRegisterAuditEnum;
use App\Enums\ActivityRegisterStatusEnum;
use App\Enums\ActivityStopSellingEnum;
use App\Models\ActivityRegisterModel;
use App\Repositories\Traits\RepositoryTrait;
use Closure;

class ActivityRegisterRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ActivityRegisterModel $model)
    {
        $this->model = $model;
    }

    /**
     * 生成签到码
     * @param int $len
     * @return string
     */
    protected function getSignCode($len = 8){
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        mt_srand(10000000*(double)microtime());
        for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, $lc)];
        }
        if ($this->exists(['sign_in_code' => $str])){
            return self::getSignCode($len);
        }
        return $str;
    }

    protected function getActivityRegisterNumber($activity_list){
        if (empty($activity_list)){
            return [];
        }
        //begin 支持强制引用
        $src_data = $activity_list; //传入的数据
        $ret_data = [];  //返回的数据
        $is_ref =($activity_list instanceof Closure);//检测是否强制引用传参。
        if($is_ref){
            $src_data = & $activity_list(); //传入的数据
            $ret_data = & $src_data;   //返回的数据
        }
        foreach($src_data as $key => $activity) {
            $where = ['activity_id' => $activity['id'],'status' => ['in',[ActivityRegisterStatusEnum::COMPLETED,ActivityRegisterStatusEnum::EVALUATION]],'audit' => ActivityRegisterAuditEnum::PASS];
            $register_number = $this->count($where) ?? 0;
            if (!empty($activity['max_number']) && $register_number >= $activity['max_number'])
            $activity['stop_selling']   = ActivityStopSellingEnum::STOP_SELLING;
            $ret_data[$key]             = $activity;
        }
        return  $is_ref?true:$ret_data;
    }
}


            