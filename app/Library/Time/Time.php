<?php


namespace App\Library\Time;


class Time
{
    /**
     * 获取起止时间
     * @param string $time_str
     * @return array
     */
    public static function getStartStopTime($time_str = 'today'){
        $res = ['start' => 0,'end' => 0];
        switch ($time_str){
            case 'today':
                $res['start']   = mktime(0,0,0,date('m'),date('d'),date('Y'));
                $res['end']     = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
                break;
            case 'yesterday':
                $res['start']   = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
                $res['end']     = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
                break;
            case 'thisweek':
                $res['start']   = mktime(0,0,0,date('m'),date('d')-date('w')+1,date('y'));
                $res['end']     = time();
                break;
            case 'lastweek':
                $res['start']   = mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
                $res['end']     = mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
                break;
            case 'thismonth':
                $res['start']   = mktime(0,0,0,date('m'),1,date('Y'));
                $res['end']     = mktime(23,59,59,date('m'),date('t'),date('Y'));
                break;
            case 'lastmonth':
                $res['start']   = strtotime(date('Y-m-01 00:00:00',strtotime('-1 month')));
                $res['end']     = strtotime(date("Y-m-d 23:59:59", strtotime(-date('d').'day')));
                break;
            case 'thisyear':
                $res['start']   = strtotime(date("Y",time())."-1"."-1");
                $res['end']     = strtotime(date("Y",time())."-12"."-31");
                break;
            case 'lastyear':
                $res['start']   = strtotime(date('Y-01-01 00:00:00',strtotime('-1 year')));
                $res['end']     = strtotime(date("Y-12-31 23:59:59", strtotime('-1 year')));
                break;
            default:
                break;
        }
        return $res;
    }

    /**
     * 细化时间，eg：今天 20:40，
     * @param $time
     * @return false|string
     */
    public static function fineTime($time){
        $cr_time    = strtotime($time);
        $today      = Time::getStartStopTime('today');
        $yesterday  = Time::getStartStopTime('yesterday');
        if ($cr_time > $today['start'] && $cr_time < $today['end']){
            $result_time = date('今天 H:i',$cr_time);
        }else if ($cr_time > $yesterday['start'] && $cr_time < $yesterday['end']){
            $result_time = date('昨天 H:i',$cr_time);
        }else{
            $result_time = date('Y.m.d',$cr_time);
        }
        return $result_time;
    }
}