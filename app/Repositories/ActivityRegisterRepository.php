<?php


namespace App\Repositories;


use App\Models\ActivityRegisterModel;
use App\Repositories\Traits\RepositoryTrait;

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
}
            