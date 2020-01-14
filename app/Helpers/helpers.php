<?php

if(!function_exists('byRef')){
    /**
     * @desc  让参数强制引用的函数。通过闭包创建一个引用通道。
     * 任何类，如果通过静态调用，由于call_user_func_array的问题，
     * 引用都会丢失。但是闭包（Closure）是一种特别的对象，
     * 参数传递对象时，全是通过引用传递的。
     * 所以，通过此函数可以实现强制引用。
     * 为什么要强制引用？目的很简单，减少创建对象的副本，从而可以减少内存消耗。
     * 使用方法：
     * 首先，要对本助手函数文件实现自动加载
     * 在根目录composer.json文件中的"autoload"中，添加：
    "files": [
    "app/Helpers/helpers.php"
    ],
     * 如果有问题：则要通过命令行走一下
     *  $ composer dump-autoload
     *  实际使用示例如下：
     *  例如：我们要调用的源码是：
     *  $ret = SomeRepository::SomeMethod($data,$columns,$where,$order) ;
     *  其中，我们想让   $data 为引用传参,只要这样：
     *  $ret = SomeRepository::SomeMethod(byRef($data),$columns,$where,$order) ;
     *  同时，在类的 SomeMethod方法中要增加几行代码
     *  public function SomeMethod(\Closure $data,$columns,$where,$order){
     *      if(!$data instanceof Closure){ //检测闭包的有效性。
     *             Throw InvalidArgumentException("Data is not valid Closure.");
     *      }
     *      $src_data = & $data(); //通过引用从闭包中获取数据
     *  }
     *  最好的方式，那就是，引用与非引用并存。参看以下代码：
     *  public function SomeMethod($data,$columns,$where,$order){
     *      $src_data = $data; //传入的数据
     *      $ret_data = [];  //返回的数据
     *      $is_ref =($data instanceof Closure);//检测是否强制引用传参。
     *      if($is_ref){
     *          $src_data = & $data(); //传入的数据
     *          $ret_data = & $src_data;   //返回的数据
     *      }
     *      //......具体的实现代码
     *      //引用只返回BOOL，或其它的。
     *      return $is_ref ？true : $ret_data;
     * }
     *
     * 以下是经过测试的代码，运行OK：
     *
    function byRef(&$data){
        return function &()use(&$data){
            return $data;
        };
    }

    function &test($try){
        $try_data = &$try();
        return $try_data;
    }

    $data = '123456';

    $data_after = &test(byRef($data));

    $data_after = '345678';

    echo($data);   //结果输出：345678
     *
     *
     * @param $data
     * @return Closure
     */
    function byRef(&$data){ //传入引用参数
        return function &()use(&$data){ //定义闭包函数以引用返回，同时接收引用参数
            return $data;
        };
    }
}

if (!function_exists('base64UrlEncode')){
    /**
     * 对提供的数据进行urlsafe的base64编码。
     *
     * @param string $data 待编码的数据，一般为字符串
     *
     * @return string 编码后的字符串
     * @link http://developer.qiniu.com/docs/v6/api/overview/appendix.html#urlsafe-base64
     */
    function base64UrlEncode($data)
    {
        $find = array('=','+', '/');
        $replace = array('','-', '_');
        return str_replace($find, $replace, base64_encode($data));
    }
}

if (!function_exists('createArrayIndex')){

    /**
     * @desc 以键值为$index重组数组。如同给表创建索引。
     * @param array $array      源二维数组
     * @param string $index     索引列
     * @return array
     */
    function createArrayIndex($array, $index){
        $result_array = [];
        foreach($array as $item){
            $result_array[$item[$index]] = $item;
        }
        return $result_array;
    }
}