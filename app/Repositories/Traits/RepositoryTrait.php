<?php


namespace App\Repositories\Traits;


use Illuminate\Database\Eloquent\Model;
use Tolawho\Loggy\Facades\Loggy;

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
        $model = self::addWhere($this->model,$where);
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
    protected function getOrderOne(array $where,string $order,string $desc='desc',array $column=['*']){
        $model = self::addWhere($this->model,$where);
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
        $model = self::addWhere($this->model,$where);
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
     * 更新一条数据并返回id
     * @param $where
     * @param $data
     * @return null
     */
    protected function getUpdId(array $where,array $data){
        $model = self::addWhere($this->model,$where);
        $result = $model->update($data);
        $id = $this->getField($where,$this->getPrimaryKey());
        return $result>=0 ? $id : null;
    }

    /**
     * 批量更新数据
     * @param $where
     * @param $data
     * @return null
     */
    protected function update(array $where,array $data){
        $model = self::addWhere($this->model,$where);
        $result = $model->update($data);
        return $result;
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

    /**
     * 删除数据
     * @param $where
     * @return null
     */
    protected function delete(array $where){
        $model = self::addWhere($this->model,$where);
        try{
            $result = $model->delete();
        }catch (\Exception $e){
            Loggy::write('error',$e);
            return null;
        }
        return $result ? $result : null;
    }

    /**
     * 返回某一列的和
     * @param $where
     * @param string $column
     * @return int
     */
    protected function sum(array $where,string $column = null){
        $column = $column ?? ($this->getPrimaryKey());
        $model = self::addWhere($this->model,$where);
        $result = $model->sum($column);
        return $result ? $result : null;
    }

    /**
     * 返回指定列的值
     * @param array $where
     * @param string $column
     * @return null
     */
    protected function getField(array $where,string $column){
        $model = self::addWhere($this->model,$where);
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
        $model = self::addWhere($this->model,$where);
        $result = $model->count();
        return $result;
    }


    /**
     * 查询数据是否存在
     * @param array $where
     * @return mixed
     */
    protected function exists(array $where){
        $model = self::addWhere($this->model,$where);
        return $model->exists();
    }


    /**
     * 获取当前repository的Model
     * @return Model
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

    /**
     * 把条件加进模型中
     * @param $model
     * @param $where
     * @return Model
     */
    private function addWhere($model, $where){
        if (!empty($where)){
            foreach ($where as $k=>$v){
                if (!is_array($v)){
                    $model = $model->where($k,$v);continue;
                }
                switch (reset($v)){
                    case 'in':
                        $model = $model->whereIn($k, end($v));
                        break;
                    default:
                        $model = $model->where($k, reset($v), end($v));
                        break;
                }
            }
        }
        return $model;
    }


    /**
     * 关键字搜索【不适合大量数据查询】
     * @param array $keywords   格式：array('搜索关键字' => array('搜索字段1','搜索字段2'))
     * @param array $where      搜索时的附加条件
     * @param array $column
     * @param null $page
     * @param null $pageNum
     * @param null $order
     * @param null $desc_asc
     * @return bool|null
     */
    protected function search(array $keywords, $where = [], $column = ['*'], $page=null, $pageNum=null, $order=null, $desc_asc=null){
        $model = $this->model;
        if (!empty($where)){
            $model = self::addWhere($this->model,$where);
        }
        foreach ($keywords as $keyword => $columns){
            if (!is_array($columns)){
                return false;
            }
            $model = $model->where(reset($columns),'like','%'.$keyword.'%');
            array_shift($columns);
            foreach ($columns as $value){
                $model = $model->orWhere($value,'like','%'.$keyword.'%');
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
}