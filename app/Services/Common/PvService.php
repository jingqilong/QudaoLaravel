<?php
namespace App\Services\Common;

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
     * 获取访问量
     * @param $type
     * @return array|bool
     */
    public function getSitePv($type){
        $where      = [];
        $last_where = [];
        switch ($type){
            case 1:#天
                $today_time     = strtotime(date('Y-m-d H:i:s'));
                $yesterday_time = $today_time - 86400;
                $where['created_at']        = ['range',[$yesterday_time,$today_time]];
                $last_where['created_at']   = ['range',[$yesterday_time - 86400,$today_time - 86400]];
                break;
            case 2:#周
                break;
            case 3:#月
                break;
            case 4:#年
                break;
            default:
                $this->setError('类型不存在！');
                return false;
        }
        $count      = CommonPvRepository::sum($where,'count') ?? 0;
        $last_count = CommonPvRepository::sum($last_where,'count');
        $growth_rate= 0;
        if ($last_count == 0){
            if ($count != 0){
                $growth_rate= 100;
            }
        }else{
            if ($count == 0){
                $growth_rate= -100;
            }else{
                $increment  = $count - $last_count;
                $percentage = round((($increment / $last_count) * 100) , 2);
                $growth_rate= (string)$percentage;
            }
        }
        $res = [
            'number'     => $count,
            'growth_rate'=> $growth_rate
        ];
        return $res;
    }
}
            