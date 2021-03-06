<?php

namespace App\Repositories;

use BadMethodCallException;
use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * @desc 这是一个基于数据表的枚举类。继承此类以后，即可以通过以下方式：
 *  SomeRepository::EnumName(); 注意：EnumName 应当是表中的name 字段。
 *  并且应当全部大写。
 *  比如： MemberRepository::NORMAL()
 *  但是，与普通枚举不同，Laravel会对不存在的常量进行检查，所以，
 *  MemberRepository::NORMAL 调用不了。
 *  如果id, label, name 字段有所不同，则需要在子类中进行配置
 *  获得对应记录的ID.此类使用了Cache。并且，所有枚举都是写在静态变量中的。
 *  并且，可以按Cache的TTL更新.
 *  不足之处，由于是基于macro，所以，在IDE编辑器没有自动完成。
 *
 * Class EnumerableRepository
 * @package App\Repositories
 */
class EnumerableRepository
{
    /**
     * @var int Cache生命周期（秒）
     */
    protected $ttl = 3600;

    /**
     * @var array 数据字段映射 （如果不一致，子类中要重定义，
     * 格式 : [''id'=>'表中的实际字段名','label'=>'表中的实际字段名','name'=>'表中的实际字段名']）
     */
    protected $columns_map = ['id'=>'id','label'=>'label','name'=>'name'];

    /**
     * 缓存字段前缀
     */
    protected const KEY_PREF = 'enum_list::';

    protected const KEY_IDS =  'id_list';

    protected const KEY_NAMES= 'name_list';

    /**
     * The registered string macros.
     *
     * @var array
     */
    protected static $macros = [];

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        // 以下是  MemberRepository::NORMAL() 这样的调用，如果有了$macro枚举，先处理
        if (static::hasMacro($method)) {
            $macro = static::$macros[$method];
            if ($macro instanceof Closure) {
                return call_user_func_array(Closure::bind($macro, null, static::class), $parameters);
            }
            return $macro(...$parameters);
        }

        //创建类，读取数据。
        $instance = app(get_called_class());

        //如果不是静态调用。则先要添加枚举的宏。
        $instance->addEnumMacros();
        return $instance->$method(...$parameters);
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if(method_exists($this,$method)){
            return $this->$method(...$parameters);
        }

        if (! static::hasMacro($method)) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.', static::class, $method
            ));
        }

        $macro = static::$macros[$method];

        if ($macro instanceof Closure) {
            return call_user_func_array($macro->bindTo($this, static::class), $parameters);
        }

        return $macro(...$parameters);

    }

    /**
     * Register a custom macro.
     *
     * @param  string $name
     * @param  object|callable  $macro
     *
     * @return void
     */
    public static function macro($name, $macro)
    {
        static::$macros[$name] = $macro;
    }

    /**
     * Checks if macro is registered.
     *
     * @param  string  $name
     * @return bool
     */
    public static function hasMacro($name)
    {
        //先拦截过期，保证可用的都有效
        $key = static::KEY_PREF . get_called_class();
        if (!Cache::has($key)) {
            return false;
        }
        return isset(static::$macros[$name]);
    }

    /**
     * @desc 获取数据列表，并存于缓存当中
     * 此函数基于 trait中的有 getAll() 和  createArrayIndex() 方法！！！！
     * @param $level ,递归时用的标记
     * @return mixed
     */
    protected function getEnumList($level = 0){
        $key = static::KEY_PREF . get_called_class();
        if (!Cache::has($key)) {
            $data_list = $this->getAll();
            $enum_list[static::KEY_IDS] = $this->createArrayIndex($data_list,$this->columns_map['id']);
            $enum_list[static::KEY_NAMES] = $this->createArrayIndex($data_list,$this->columns_map['name']);
            Cache::put($key,$enum_list,$this->ttl);
        }
        $list = Cache::get($key);
        //数据出问题时，重新加载缓存
        if(!isset($list[static::KEY_IDS])){
            Cache::forget($key);
            if(0 == $level){
                return $this->getEnumList(1);
            }
        }
        return Cache::get($key);
    }

    /**
     * @return mixed
     */
    protected function getIdList(){
        $list = $this->getEnumList();
        return $list[static::KEY_IDS];
    }

    /**
     * @return array
     */
    protected function getIdArray(){
        $list = $this->getEnumList();
        return array_keys($list[static::KEY_IDS]);
    }


    /**
     * @return mixed
     */
    protected function getNameList(){
        $list = $this->getEnumList();
        return $list[static::KEY_NAMES];
    }

    /**
     * @return array
     */
    protected function getNameArray(){
        $list = $this->getEnumList();
        return array_keys($list[static::KEY_NAMES]);
    }

    /**
     * @desc 添加枚举的宏
     *
     * @return bool
     */
    public function addEnumMacros(){
        $key = static::KEY_PREF . get_called_class();
        if((Cache::has($key) && count(static::$macros)>0)){
            return true;
        }
        $list = $this->getEnumList();
        foreach($list[static::KEY_IDS] as $key => $value){
            $this->macro( //改枚举的方法名为大写
                strtoupper($value[$this->columns_map['name']]) ,
                function ()use($key){ //枚举宏
                    return $key;
                }
            );
        }
        return true;
    }

    /**
     * @通过枚举的名称获取ID
     * @param $name
     * @param $default
     * @return mixed
     */
    protected function getIdByName($name,$default = 0){
        $enum_list =  $this->getEnumList();
        if(!isset( $enum_list[static::KEY_NAMES][$name])){
            return $default;
        }
        $name_data =  $enum_list[static::KEY_NAMES][$name];
        return $name_data[$this->columns_map['name']];
    }

    /**
     * @desc 通过ID获取label
     * @param $id
     * @param $default
     * @return mixed
     */
    protected function getLabelById($id,$default =''){
        $enum_list =  $this->getEnumList();
        if(!isset( $enum_list[static::KEY_IDS][$id])){
            return $default;
        }
        $id_data =  $enum_list[static::KEY_IDS][$id];
        return $id_data[$this->columns_map['label']];
    }

    /**
     * @desc 通过名称 获取一条枚举数据记录
     * @param $name
     * @param string $default
     * @return mixed
     */
    protected function getOneByName($name, $default = ''){
        $enum_list =  $this->getEnumList();
        if(!isset( $enum_list[static::KEY_NAMES][$name])){
            $name = $default;
        }
        if(!isset( $enum_list[static::KEY_NAMES][$name])){
            $enum_list = reset($enum_list[static::KEY_NAMES]);
        }else{
            $enum_list =  $enum_list[static::KEY_NAMES][$name];
        }
        return $enum_list;
    }

    /**
     * @desc 通过id 获取一条枚举数据记录
     * @param $id
     * @param $default
     * @return mixed
     *
     */
    protected function getOneById($id,$default = 0){
        $enum_list =  $this->getEnumList();
        if(!isset( $enum_list[static::KEY_IDS][$id])){
            $id = $default;
        }
        if(!isset( $enum_list[static::KEY_IDS][$id])){
            $id_data = reset($enum_list[static::KEY_IDS]);
        }else{
            $id_data =  $enum_list[static::KEY_IDS][$id];
        }
        return  $id_data;
    }

    /**
     * @param $id
     * @return bool
     */
    protected function hasID($id){
        $enum_list =  $this->getEnumList();
        if(!isset( $enum_list[static::KEY_IDS][$id])){
            return false;
        }
        return true;
    }
}