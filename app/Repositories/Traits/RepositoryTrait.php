<?php


namespace App\Repositories\Traits;


use Illuminate\Database\Eloquent\Model;

trait RepositoryTrait
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * 获取主键
     * @return string
     */
    protected function getPrimaryKey(){
        $model = $this->model;
        $pk = 'id';
        if(null !== $model->getKeyName()){
            $pk = $model->getKeyName();
        }
        return $pk;
    }

    /**
     * 获取一条数据
     * @param array $where
     * @param array $column
     * @return null
     */
    protected function getOne(array $where,array $column=['*']){
        $model = $this->model;
        foreach ($where as $k => $v) {
            if(is_array($v)){
                $model = $model->where($k, reset($v), end($v));
            }else{
                $model = $model->where($k,$v);
            }
        }
        //$model = $model->where($where);
        $result = $model->first($column);
        return $result ? $result->toArray() : null;
    }

    /**
     * 返回主键对应的记录
     * @param $key
     * @return array|null
     */
    protected function find($key){
        $model = $this->model;
        $result = $model->find($key);
        return $result ? $result->toArray() : null;
    }

    /**
     * 获取排序后的第一条数据
     * @param array $where
     * @param string $order
     * @param string $desc
     * @param array $column
     * @return null
     */
    protected function getOrderOne(array $where=[], $order='*', $desc='desc',array $column=['*']){
        $model = $this->model;
        foreach ($where as $name => $value){
            if (is_array($value)){
                $model = $model->where($name,reset($value),end($value));
            }else{
                $model = $model->where($name, $value);
            }
        }
        $result = $model->orderBy($order,$desc)->first($column);
        return $result ? $result->toArray() : null;
    }

    /**
     * 获取所有数据
     * @param array $column
     * @return null
     */
    protected function getAll(array $column=['*']){
        $result=$this->model->all($column);
        return $result ? $result->toArray() : null;
    }

    /**
     * 获取数据列表
     * @param array $where
     * @param array $column
     * @param null $order
     * @param null $desc_asc
     * @param null $page
     * @param null $pageNum
     * @return null
     */
    protected function getList(array $where=['1'=>1],array $column=['*'], $order=null, $desc_asc=null, $page=null, $pageNum=null){
        $model = $this->model;
        foreach ($where as $k=>$v){
            if (is_array($v)){
                switch (reset($v)){
                    case 'in':
                        $model = $model->whereIn($k, end($v));
                        break;
                    default:
                        $model = $model->where($k, reset($v), end($v));
                        break;
                }
            }else{
                $model = $model->where($k,$v);
            }
        }
        if ($order!=null && $desc_asc!=null){
            $model = $model->orderBy($order,$desc_asc);
        }
        if (null!=$page && null!=$pageNum){
            $model = $model->paginate($pageNum,$column,'*',$page);
            return $model ? $model->toArray() : null;
        }
        $result = $model->get($column);
        return $result ? $result->toArray() : null;
    }

    /**
     * 添加一条数据并返回数据id
     * @param $data
     * @return null
     */
    protected function getAddId(array $data=[]){
        $result=$this->model->insertGetId($data);
        return $result>0 ? $result : null;
    }

    /**
     * 批量添加数据
     * @param $data
     * @return null
     */
    protected function create($data){
        $result=$this->model->insert($data);
        return $result ? $result : null;
    }

    /**
     * 更新数据并返回id
     * @param $where
     * @param $data
     * @return null
     */
    protected function getUpdId(array $where,array $data){
        $model = $this->model;
        foreach ($where as $name => $value){
            if (is_array($value)){
                $model = $model->where($name,reset($value),end($value));
            }else{
                $model = $model->where($name, $value);
            }
        }
        $result = $model->update($data);
        $id = $this->getField($where,$this->getPrimaryKey());
        return $result>=0 ? $id : null;
    }

    /**
     * 获取第一条查询到的数据，如果不存在，则创建数据
     * @param $where
     * @param $data
     * @return null
     */
    protected function firstOrCreate(array $where,array $data){
        $result=$this->model->firstOrCreate($where,$data);
        return $result ? 1 : null;
    }

    /**删除数据
     * @param $where
     * @return null
     */
    protected function delete(array $where){
        $model = $this->model;
        foreach ($where as $name => $value){
            if (is_array($value)){
                $model = $model->where($name,reset($value),end($value));
            }else{
                $model = $model->where($name, $value);
            }
        }
        $result = $model->delete();
        return $result ? $result : null;
    }

    /**
     * 返回某一列的和
     * @param $where
     * @param string $column
     * @return int
     */
    protected function sum(array $where, $column='*'){
        $model = $this->model;
        foreach ($where as $name => $value){
            if (is_array($value)){
                $model = $model->where($name,reset($value),end($value));
            }else{
                $model = $model->where($name, $value);
            }
        }
        $result = $model->sum($column);
        return $result ? $result : null;
    }

    /**
     * 返回指定列的值
     * @param array $where
     * @param string $column
     * @return null
     */
    protected function getField(array $where=[], $column = '*'){
        $model = $this->model;
        foreach ($where as $name => $value){
            if (is_array($value)){
                $model = $model->where($name,reset($value),end($value));
            }else{
                $model = $model->where($name, $value);
            }
        }
        $result = $model->first([$column]);
        return $result ? $result->toArray()[$column] : null;
    }

    /**
     * 统计数据条数
     * @param $where
     * @return null
     */
    protected function count(array $where)
    {
        $model=$this->model;
        if (!empty($where) && is_array($where))
            foreach ($where as $k => $v) {
                if (is_array($v)) {
                    $model = $model->where($k, reset($v), end($v));
                } else {
                    $model = $model->where($k,$v);
                }
            }
        $result = $model->count();
        return $result;
    }


    /**
     * 查询数据是否存在
     * @param array $where
     * @return mixed
     */
    protected function exists(array $where){
        $model = $this->model;
        foreach ($where as $name => $value){
            if (is_array($value)){
                $model = $model->where($name,reset($value),end($value));
            }else{
                $model = $model->where($name, $value);
            }
        }
        return $model->exists();
    }


    /**
     * 获取当前repository的Model
     * @return mixed
     */
    protected function model(){
        return $this->model;
    }

    /**
     * 获取当前模型的公开列名
     * @return mixed
     */
    protected function getFields()
    {
        return $this->model->getFillable();
    }
}