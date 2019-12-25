<?php


namespace App\Repositories;


use App\Models\OaProcessCategoriesModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessCategoriesRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessCategoriesModel $model)
    {
        $this->model = $model;
    }

    /**
     * @param $category_id
     * @return mixed;
     */
    protected function isEnabled($category_id){
        $action = $this->getOne(['id'=>$category_id]);
        if(!$action){
            return ['code'=>100,'message'=>"抱歉，此流程分类仍未定义！"];
        }
        if(1==$action['status']){
            return ['code'=>100,'message'=>"抱歉，此流程分类已被禁用！"];
        }
        return ['code'=>200,'message'=>"此流程分类存在且可用！"];
    }
}
            