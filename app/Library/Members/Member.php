<?php


namespace App\Library\Members;

use RuntimeException;

/**
 * Class Member
 * @package App\Library\Members
 */
class Member
{


    /**
     * @desc 通过配置文件指定相关的类，从而创建对应的策略对象的静态方法
     * @example  $member = Member::of($member_grade);
     * @param $member_grade
     * @return \Illuminate\Contracts\Foundation\Application|mixed
     */
    public static function of($member_grade){
        $class = config('member.member_class');
        $member  =  app($class[$member_grade]);
        $member->setGrade($member_grade);
        return $member;
    }

    /**
     * @desc 提供快捷的静态调用接口。但注意：用此方式调用时，参数中要多加一个参数在参数表前面。
     * 比如： 原来：$discount = Member::of($member_grade)->discount($amount);
     * 通过此接口调用时，则是： $discount = Member::discount($member_grade,$amount);
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        $member_grade = array_shift($parameters);
        $instance = self::of($member_grade);
        if(!method_exists($instance,$method)){
            throw new RuntimeException('Member::' . $method ."方法不存在！");
        }
        return $instance->$method(...$parameters);
    }








}