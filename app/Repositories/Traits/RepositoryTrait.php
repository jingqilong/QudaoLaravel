<?php


namespace App\Repositories\Traits;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Tolawho\Loggy\Facades\Loggy;
use Closure;

trait RepositoryTrait
{

    /**
     * @var Model
     */
    protected $model;

    protected function setPage($num ){
        $request = request();
        $request['page'] = $num;
    }

    protected function setPerPage($num ){
        $request = request();
        $request['page_num'] = $num;
    }

    /**
     * 获取当前传入的分页参数
     * 如果要分开到变量中：list($page,$page_num) = $this->inputPage();
     * @param int $per_page
     * @return array
     */
    public function inputPage($per_page = 20){
        return [
            request('page',1),
            request('page_num',$per_page)
        ];
    }

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
     * @param array $where
     * @param array $order
     * @param array $desc
     * @param array $column
     * @return array
     */
    protected function getFirst(array $where,array $order,array $desc,array $column=['*']){
        $this->setPerPage(1);
        $result = $this->getList($where,$column,$order, $desc);
        if(isset($result['data'][0])){
            return $result['data'][0];
        }
        return [];
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
     * @desc 查询所有列表数据
     * @param array $where
     * @param array $column
     * @param null $order
     * @param null $desc_asc
     * @return  array|null
     */
    protected function getAllList(array $where=['1'=>1],array $column=['*'], $order=null, $desc_asc=null){
        $model = self::addWhere($this->model,$where);
        if ($order!=null && $desc_asc!=null){
            $model = $this->addOrderBy($model,$order,$desc_asc);
        }
        $result = $model->get($column);
        return $result ? $result->toArray() : null;
    }

    /**
     * @desc 查询count数据
     * @param int $count
     * @param array $where
     * @param array $column
     * @param null $order
     * @param null $desc_asc
     * @return  array|null
     */
    protected function getEnableQueryCount(int $count ,array $where=['1'=>1],array $column=['*'], $order=null, $desc_asc=null){
        $model = self::addWhere($this->model,$where);
        if ($order!=null && $desc_asc!=null){
            $model = $this->addOrderBy($model,$order,$desc_asc)->limit($count);
        }
        $result = $model->get($column);
        return $result ? $result->toArray() : null;
    }

    /**
     * 获取数据列表
     * @param array $where
     * @param array $column
     * @param null $order
     * @param null $desc_asc
     * @return null
     */
    protected function getList(array $where=['1'=>1],array $column=['*'], $order=null, $desc_asc=null){
        list($page,$page_num) = $this->inputPage();
        $model = self::addWhere($this->model,$where);
        if ($order!=null && $desc_asc!=null){
            $model = $this->addOrderBy($model,$order,$desc_asc);
        }
        $model = $model->paginate($page_num,$column,'*',$page);
        return $model ? $model->toArray() : null;
    }

    /**
     * 获取随机返回的count数据
     * @param int $count
     * @param array $column
     * @param array $where
     * @return |null
     */
    protected function getRandomCount(int $count , array $column=['*'], array $where=[]){
        $model = self::addWhere($this->model,$where);
        $model = $model->inRandomOrder()->take($count)->get($column);
        return $model ? $model->toArray() : null;
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
        return $result ? $result->toArray() : null;
    }


    /**
     * 获取第一条查询到的数据，如果不存在，则创建数据
     * @param array $where
     * @param array $data
     * @return bool|null
     */
    protected function updateOrInsert(array $where, array $data){
        if (!$model = self::addWhere($this->model,$where)->exists()) {
            $result = $this->model->insert(array_merge($where, $data));
            return $result ? $result : null;
        }
        if (empty($data)) {
            return true;
        }
        $model = self::addWhere($this->model,$where);
        $result = $model->update($data);
        return $result;
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
     * @param Model $model
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
                    case 'notIn':
                        $model = $model->whereNotIn($k, end($v));
                        break;
                    case 'range':
                        $range = end($v);
                        $model = $model->whereRaw($k.' > '.reset($range) . ' and ' . $k .' < '. end($range));
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
     * @param null $order
     * @param null $desc_asc
     * @return mixed
     */
    protected function searchAll(array $keywords, $where = [], $column = ['*'], $order=null, $desc_asc=null){
        $model = $this->model;
        if (!empty($where)){
            $model = self::addWhere($this->model,$where);
        }
        foreach ($keywords as $keyword => $columns){
            if (!is_array($columns)){
                return false;
            }
            $model = $model->where(function ($query)use ($columns,$keyword){
                foreach ($columns as $value) {
                    $query->orWhere($value, 'like', '%' . $keyword . '%');
                }
            });
        }
        if ($order!=null && $desc_asc!=null){
            $model = $this->addOrderBy($model,$order,$desc_asc);
        }
        $result = $model->get($column);
        return $result ? $result->toArray() : null;
    }


    /**
     * 关键字搜索【不适合大量数据查询】
     * @param array $keywords   格式：array('搜索关键字' => array('搜索字段1','搜索字段2'))
     * @param array $where      搜索时的附加条件
     * @param array $column
     * @param null $order
     * @param null $desc_asc
     * @return mixed
     */
    protected function search(array $keywords, $where = [], $column = ['*'], $order=null, $desc_asc=null){
        list($page,$page_num) = $this->inputPage();
        $model = $this->model;
        if (!empty($where)){
            $model = self::addWhere($this->model,$where);
        }
        foreach ($keywords as $keyword => $columns){
            if (!is_array($columns)){
                return false;
            }
            $model = $model->where(function ($query)use ($columns,$keyword){
                foreach ($columns as $value) {
                    $query->orWhere($value, 'like', '%' . $keyword . '%');
                }
            });
        }
        if ($order!=null && $desc_asc!=null){
            $model = $this->addOrderBy($model,$order,$desc_asc);
        }
        $model = $model->paginate($page_num,$column,'*',$page);
        return $model ? $model->toArray() : null;
    }

    /**
     * 获取指定列表
     * @param array $ids
     * @param array $column
     * @param string $where_id
     * @return array|null
     */
    protected function getAssignList(array $ids,$column=['*'],$where_id='id'){
        if (empty($ids)){
            return [];
        }
        $all_ids = [];
        foreach ($ids as $str){
            $str_arr = explode(',',$str);
            $all_ids = array_merge($all_ids,$str_arr);
        }
        $all_ids = array_unique($all_ids);
        $list = $this->getAllList([$where_id => ['in',$all_ids]],$column);
        return $list;
    }

    /**
     * 给指定列减去一点数量，指定列必须为数字
     * @param $where
     * @param string $column    指定列
     * @param int $number       减量
     * @return int
     */
    protected function decrement($where, $column, $number = -1){
        $model = self::addWhere($this->model,$where);
        return $model->increment($column,$number);
    }
    /**
     * 给指定列增加一定数量，指定列必须为数字
     * @param $where
     * @param string $column    指定列
     * @param int $number       增量
     * @return int
     */
    protected function increment($where,$column,$number = 1){
        $model = self::addWhere($this->model,$where);
        return $model->increment($column,$number);
    }

    protected function addOrderBy(&$model,$order=null, $desc_asc=null){
        if (empty($order) || empty($desc_asc)){
            return $model;
        }
        if (is_string($order) && is_string($desc_asc)){
            $model->orderBy($order,$desc_asc);
        }
        if (is_array($order) && is_array($desc_asc)){
            foreach ($order as $key => $value){
                $model->orderBy($value,$desc_asc[$key]);
            }
        }
        return $model;
    }

    /**
     * @desc 以键值为$key重组数组。如同给表创建索引。
     * @param $src_array ,源二组数组。
     * @param $index_column ,要索引的列
     * @return array
     */
    protected function createArrayIndex($src_array,$index_column){
        $result_array = [];
        foreach($src_array as $item){
            $result_array[$item[$index_column]] = $item;
        }
        return $result_array;
    }

    /**
     * @desc 以联表条件，查出所有的相关数据。
     * @param $src_list ,源列表数据（主表），从表是当前的Repository
     * @param array $where 附加的 where条件
     * @param $join    ,关联条件 ['from' => '$src_list中的字段' ，'to' => '当前Repository中的字段' ]
     * @param $columns  ,要查出的字段列表
     * @return array|null
     */
    protected function getHasOneList($src_list,$where, $join, $columns){
        $in_param = Arr::pluck($src_list,$join['from']);
        $in_param = array_unique($in_param);
        $where[$join['to']] =['in', $in_param];
        $data = $this->getAllList($where,$columns,$join['to'],'asc');
        if(!$data){
            return [];
        }
        return  $this->createArrayIndex($data,$join['to']);
    }

    /**
     * @desc 以联表条件，查出所有的相关数据。(一对多，源表中是逗号分隔的)
     * @param $src_list ,源列表数据（主表），从表是当前的Repository
     * @param array $where 附加的 where条件
     * @param $join    ,关联条件 ['from' => '$src_list中的字段' ，'to' => '当前Repository中的字段' ]
     * @param $columns  ,要查出的字段列表
     * @return array|null
     */
    protected function getHasManyList($src_list,$where, $join, $columns){
        $param_array = Arr::pluck($src_list,$join['from']);
        array_walk($param_array, function(&$value) {
            $value = trim($value,',' );
        });
        $in_param_str = implode(',',$param_array);
        $in_param = array_unique(explode(',',$in_param_str));
        $where[$join['to']] =['in', $in_param];
        $data = $this->getAllList($where,$columns,$join['to'],'asc');
        if(!$data){
            return [];
        }
        return $this->createArrayIndex($data,$join['to']);
    }

    /**
     * @desc 批量设置字段，代替联表查询，此函数默认只用调用 getAllList 处理
     * 强制引用的实例，参见：ShopInventorService::getInventorList 方法
     * @param $src_list  ,源列表 作为主表，如果要强制引用，则使用函数 byRef($src_list)传参
     * @param $join ,关联条件 ['from' => '$src_list中的字段' ，'to' => '当前Repository中的字段' ]
     * @param $alias  ,别名：将查出结果更名为什么 ['联接的name1' => '主表alias1','联接的name2' => '主表alias2']
     * @param array $where 附加的 where条件
     * @return bool  ,结果列表，强制引用时则是bool，操作成功与否
     */
    protected function bulkHasOneSet( $src_list, $join, $alias, $where=[]){
        //begin 支持强制引用
        $src_data = $src_list; //传入的数据
        $is_ref =($src_list instanceof Closure);//检测是否强制引用传参。
        if($is_ref){
            $src_data = & $src_list(); //传入的数据
        }
        //end 支持强制引用
        if(!$set_data = $this->getHasOneList($src_data,$where,$join, array_keys($alias))){
            $set_data = [];
        }
        foreach($src_data as & $src_item) {
            $key = $src_item[$join['from']]??"";
            foreach($alias as $src => $trg){
                 $src_item[$trg] = $set_data[$key][$src]??"";
            }
         }
         return $is_ref?true:$src_data;
    }

    /**
     * @desc 批量查询字段，代替联表查询，此函数默认只要调用 getAllList 处理，交给闭包处理
     * @example
     *
     *  $result_list = SomeRepository::bulkHasOneWalk(
     *      $src_list,
     *      $join,
     *      $columns,
     *      $where,
     *      function($src_item,$set_item){
     *           //可以用 dd 查看参数
     *           dd($src_item,$set_item);
     *           //最关键，还是你不需要foreach，直接在这个位置写单条记录的处理方法。
     *  });
     *
     *  //你还可以跟你的类一起使用，或用你函数中的其它变量
     *  $result_list = SomeRepository::bulkHasOneWalk(
     *      $src_list,
     *      $join,
     *      $columns,
     *      $where,
     *      function($src_item,$set_items)use($this,$other_param){
     *           //可以用 dd 查看参数
     *           dd($src_item,$set_items);
     *           //最关键，还是你不需要foreach，直接在这个位置写单条记录的处理方法。
     *  });
     *
     * 强制引用的实例，参见：ShopInventorService::getInventorList 方法
     *
     * @param $src_list  ,源列表 作为主表， 如果要强制引用，则使用函数 byRef($src_list)传参
     * @param $join ,关联条件 ['from' => '$src_list中的字段' ，'to' => '当前Repository中的字段' ]
     * @param $columns  ,要从当前Repository中查出的字段
     * @param array $where 附加的 where条件
     * @param $callback ,闭包，用来实现结果的处理
     * @return bool|array  ,,结果列表，强制引用时则是bool，操作成功与否
     *
     */
    protected function bulkHasOneWalk($src_list, $join, $columns, $where,Closure $callback){
        //begin 支持强制引用
        $src_data = $src_list; //传入的数据
        $ret_data = [];  //返回的数据
        $is_ref =($src_list instanceof Closure);//检测是否强制引用传参。
        if($is_ref){
            $src_data = & $src_list(); //传入的数据
            $ret_data = & $src_data;   //返回的数据
         }
        //end 支持强制引用
        $default_set = $this->fillArrayWithKeys($columns);
        if(!$set_data = $this->getHasOneList($src_data,$where,$join, $columns)){
            $set_data = [];
        }
        foreach($src_data as $list_key => $src_item) {
            $key = $src_item[$join['from']];
            $ret_data[$list_key] = $callback($src_item,$set_data[$key] ?? $default_set);
        }
        return  $is_ref?true:$ret_data;
    }

    /**
     * @desc 批量查询字段，代替联表查询，此函数默认只要调用 getAllList 处理，交给闭包处理
     * @example
     *
     *  $result_list = SomeRepository::bulkHasManyWalk(
     *      $src_list,
     *      $join,
     *      $columns,
     *      $where,
     *      function($src_item,$set_items){
     *           //可以用 dd 查看参数
     *           dd($src_item,$set_items);
     *           //最关键，还是你不需要foreach，直接在这个位置写单条记录的处理方法。
     *  });
     *
     *  //你还可以跟你的类一起使用，或用你函数中的其它变量
     *  $result_list = SomeRepository::bulkHasManyWalk(
     *      $src_list,
     *      $join,
     *      $columns,
     *      $where,
     *      function($src_item,$set_items)use($this,$other_param){
     *           //可以用 dd 查看参数
     *           dd($src_item,$set_items);
     *           //最关键，还是你不需要foreach，直接在这个位置写单条记录的处理方法。
     *  });
     *
     * 强制引用的实例，参见：ShopInventorService::getInventorList 方法
     *
     * @param $src_list  ,源列表 作为主表，如果要强制引用，则使用函数 byRef($src_list)传参
     * @param $join ,关联条件 ['from' => '$src_list中的字段' ，'to' => '当前Repository中的字段' ]
     * @param $columns  ,要从当前Repository中查出的字段
     * @param array $where 附加的 where条件
     * @param $callback ,闭包，用来实现结果的处理
     * @return bool|array  ,结果列表，强制引用时则是bool，操作成功与否
     *
     */
    protected function bulkHasManyWalk($src_list, $join, $columns, $where,Closure $callback){
        //begin 支持强制引用
        $src_data = $src_list; //传入的数据
        $ret_data = [];  //返回的数据
        $is_ref =($src_list instanceof Closure);//检测是否强制引用传参。
        if($is_ref){
            $src_data = & $src_list(); //传入的数据
            $ret_data = & $src_data;   //返回的数据
        }
        //end 支持强制引用
        $default_set = [$this->fillArrayWithKeys($columns)];
        if(!$set_data = $this->getHasManyList($src_data,$where,$join, $columns)){
            $set_data = [];
        }
        foreach($src_data as $list_key => $src_item) {
            $keys = $src_item[$join['from']]??'';
            $set_items = Arr::Only($set_data, explode(",",trim($keys,',')));
            $set_items = $set_items??$default_set;
            $ret_data[$list_key] = $callback($src_item,$set_items);
        }
        return $is_ref?true:$ret_data;
    }

    /**
     * @param array $keys
     * @param string $value
     * @return array|false
     */
    protected function fillArrayWithKeys(array $keys,$value=''){
        $values = array_fill(0, count($keys), $value);
        return array_combine($keys, $values);
    }

}