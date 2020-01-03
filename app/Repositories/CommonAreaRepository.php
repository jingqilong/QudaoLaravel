<?php


namespace App\Repositories;


use App\Models\CommonAreaModel;
use App\Repositories\Traits\RepositoryTrait;

class CommonAreaRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(CommonAreaModel $model)
    {
        $this->model = $model;
    }

    /**
     * 地区码转地区名
     * @param array|string $codes
     * @return array|string
     */
    protected function codeTransName($codes){
        if (empty($codes)){
            return '';
        }
        $code_arr = [];
        $result   = '';
        if (is_array($codes)){
            $code_arr = $codes;
        }
        if (is_string($codes)){
            $code_arr = explode(',',$codes);
        }
        if (!$area_list = $this->getList(['code' => ['in',$code_arr]],['code','name'])){
            return '';
        }
        foreach ($area_list as $area){
            foreach ($code_arr as $code){
                if ($code == $area['code']){
                    $result .= $area['name'];
                    break;
                }
            }
        }
        return $result;
    }
}
            