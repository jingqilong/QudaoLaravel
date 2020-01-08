<?php


namespace App\Library\Members;

/**
 * Class Member
 * @package App\Library\Members
 */
class Member
{
    public static function of($member_grade){
        $class = config('member.member_class');
        $member  =  app($class[$member_grade]);
        $member->setGrade($member_grade);
        return $member;
    }









}