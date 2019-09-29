<?php


namespace App\Repositories;


use App\Models\MemberSpecifyViewModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberSpecifyViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberSpecifyViewModel $model)
    {
        $this->model = $model;
    }

    /**
     * 软删除
     * @param array $where
     * @return bool|null
     */
    protected function delete(array $where)
    {
        $model = $this->model;
        foreach ($where as $name => $value){
            if (is_array($value)){
                $model = $model->where($name,reset($value),end($value));
            }else{
                $model = $model->where($name, $value);
            }
        }
        $result = $model->update(['deleted_at' => time()]);
        return $result>=0 ? true : null;
    }
}
            