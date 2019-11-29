<?php
namespace App\Services\Common;

use App\Library\Time\Time;
use App\Repositories\CommonPvRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Cache;

class PvService extends BaseService
{
    /**
     * 记录访问量
     * @return bool
     */
    public static function recordPV(){
        $key            = 'record_pv';
        $today_time     = strtotime(date('Y-m-d'));
        if (!Cache::has($key)){
            Cache::forever($key,[['count' => 1,'created_at' => $today_time]]);
            return true;
        }
        $data = Cache::get($key);
        array_push($data,
            [
                'count' => 1,
                'created_at' => $today_time
            ]);
        Cache::forever($key,$data);
        return true;
    }

    /**
     * 缓存数据存入数据库失败时，把数据归还给缓存
     * @param $key
     * @param $data
     * @return bool
     */
    public static function returnData($key, $data){
        $new_data = Cache::has($key) ? Cache::get($key) : [];
        array_push($new_data,$data);
        Cache::forever($key,$data);
        return true;
    }

    /**
     * 获取访问量
     * @param $type
     * @return array|bool
     */
    public function getSitePv($type){
        $where      = [];
        $last_where = [];
        switch ($type){
            case 1:#天
                $now_time   = 'today';
                $past_time  = 'yesterday';
                break;
            case 2:#周
                $now_time   = 'thisweek';
                $past_time  = 'lastweek';
                break;
            case 3:#月
                $now_time   = 'thismonth';
                $past_time  = 'lastmonth';
                break;
            case 4:#年
                $now_time   = 'thisyear';
                $past_time  = 'lastyear';
                break;
            default:
                $this->setError('类型不存在！');
                return false;
        }
        $now                        = Time::getStartStopTime($now_time);
        $past                       = Time::getStartStopTime($past_time);
        $where['created_at']        = ['range',[$now['start']-1,$now['end']]];
        $last_where['created_at']   = ['range',[$past['start']-1,$past['end']]];
        $count                      = CommonPvRepository::sum($where,'count') ?? 0;
        $last_count                 = CommonPvRepository::sum($last_where,'count') ?? 0;
        if ($last_count  == 0 && $count != 0){
            $growth_rate = 100;
        }elseif($last_count != 0 && $count == 0){
            $growth_rate = -100;
        }elseif($last_count != 0 && $count != 0){
            $increment   = $count - $last_count;
            $percentage  = round((($increment / $last_count) * 100) , 2);
            $growth_rate = (string)$percentage;
        }else{
            $growth_rate = 0;
        }
        $res = [
            'last_number' => $last_count,   #上个时间段数量
            'number'      => $count,        #当前时间段数量
            'growth_rate' => $growth_rate   #增长百分比
        ];
        $this->setMessage('获取成功！');
        return $res;
    }
}
            