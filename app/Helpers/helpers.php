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
     * 例如：我们要调用的源码是：
     *  $ret = SomeRepository::SomeMethod($data,$columns,$where,$order) ;
     *  其中，我们想让   $data 为引用传参,只要这样：
     *  $ret = SomeRepository::SomeMethod(byRef($data),$columns,$where,$order) ;
     *  同时，在类的 SomeMethod方法中要增加几行代码
     *  public function SomeMethod(\Closure $data,$columns,$where,$order){
     *      if(!$data instanceof Closure){ //检测闭包的有效性。
     *             Throw InvalidArgumentException("Data is not valid Closure.");
     *      }
     *      $src_data = & $data(); //通过引用从闭包中获取数据
     * }
     * 如果想更方便的方式： 则使用约定法。比如，约定，第一个参数就是引用。非引用的闭包参数必须在最后。
     * 在这一规则下。所有类中的引用声明，就可以使用原来的方式。
     * 而我们可以在__callStatic __call中进行拦截，将其转换为正常参数.
     * 以下是测试的代码：
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
     * //未完成，待续......
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