<?php


namespace App\Traits;


use App\Repositories\CommonAreaRepository;

trait HelpTrait
{
    /**
     * 遍历获取文件夹中的所有文件
     * @param $path
     * @return array|bool
     */
    function traverseFile($path){
        if (!is_dir($path)) {
            return false;
        }
        $file_list = scandir($path);
        $file_desc_list = [];
        foreach ($file_list as $k => $v){
            if ('.' == $v || '..' == $v){
                unset($file_list[$k]);
                continue;
            }
            $one_path = $path . '\\' . $v;
            if (is_dir($one_path)){
                $file_desc_list[$v] = $this->traverseFile($one_path);
            }
            if (is_file($one_path)){
                $file_desc_list[$v] = $one_path;
            }
        }
        return $file_desc_list;
    }


    /**
     * 数组中查询指定键值的数据
     * @param $array
     * @param $filed
     * @param $value
     * @return array|bool
     */
    function searchArray($array, $filed, $value){
        if (!in_array($value,array_column($array,$filed))){
            return false;
        }
        $res = [];
        foreach ($array as $k => $v){
            if (is_array($v) && $v[$filed] == $value){
                $res[] = $v;continue;
            }
            if ($k == $filed && $v == $value){
                $res = $array;break;
            }
        }
        return $res;
    }

    /**
     * 静态访问数组中查询指定键值的数据
     * @param $array
     * @param $filed
     * @param $value
     * @return array|bool
     */
    public static function searchArrays($array, $filed, $value){
        if (!in_array($value,array_column($array,$filed))){
            return false;
        }
        $res = [];
        foreach ($array as $k => $v){
            if (is_array($v) && $v[$filed] == $value){
                $res[] = $v;continue;
            }
            if ($k == $filed && $v == $value){
                $res = $array;break;
            }
        }
        return $res;
    }

    /**
     * 检查数组中是否存在指定键对应值
     * @param $array
     * @param $filed
     * @param $value
     * @return bool
     */
    function existsArray($array, $filed, $value){
        if (in_array($value,array_column($array,$filed))){
            return true;
        }
        return false;
    }

    function arraySearchKey($needle, $haystack){
        global $nodes_found;

        foreach ($haystack as $key1=>$value1) {

            if ($key1=== $needle){

                $nodes_found[] = $value1;

            }
            if (is_array($value1)){
                $this->arraySearchKey($needle, $value1);
            }


        }
        return $nodes_found;
    }

    /**
     * 时间格式化
     * @param integer $time
     * @param integer $type     转换类型
     * @param string $default   时间为0时的默认提示
     * @return string
     */
    function timeShift($time, $type, $default = '无限制'){
        $res = '';
        if ($time == 0){
            return $default;
        }
        switch ($type){
            case 1:
                $res = floor($time/60) . '分钟';
                break;
            case 2:
                $res = floor($time/3600) . '小时';
                break;
            case 3:
                $hours = floor($time/3600);
                $res    .= $hours == 0 ? '' : $hours . '小时';
                $time   = ($time%3600);
                $mini = floor($time/60);
                $res    .= $mini == 0 ? '' : $mini . '分钟';
                break;
            case 4:
                $day = floor($time/86400);
                $res    = $day == 0 ? '' : $day . '天';
                $time   = ($time%86400);
                $hours = floor($time/3600);
                $res    .= $hours == 0 ? '' : $hours . '小时';
                $time   = ($time%3600);
                $mini = floor($time/60);
                $res    .= $mini == 0 ? '' : $mini . '分钟';
                break;
            case 5:
                $week = floor($time/604800);
                $res    .= $week == 0 ? '' : $week . '周';
                $time   = ($time%604800);
                $day = floor($time/86400);
                $res    .= $day == 0 ? '' : $day . '天';
                $time   = ($time%86400);
                $hours = floor($time/3600);
                $res    .= $hours == 0 ? '' : $hours . '小时';
                $time   = ($time%3600);
                $mini = floor($time/60);
                $res    .= $mini == 0 ? '' : $mini . '分钟';
                break;
        }
        return $res;
    }


    /**
     * 抽奖算法
     * @param $proArr
     * @return int|string
     */
    function get_rand($proArr) {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }

    /**
     * 根据会员等级计算折扣
     * @param integer $grade 等级,1、亦享成员，2、至享成员，3、悦享成员，4、真享成员，5、君享成员，6、尊享成员，7、测试成员
     * @param $amount
     * @return int
     */
    function discount($grade, $amount){
        $discount_amount = 0;
        switch ($grade){
            case 1:
                $discount_amount = $amount;
                break;
            case 2:
                $discount_amount = $amount;
                break;
            case 3:
                $discount_amount = $amount;
                break;
            case 4:
                $discount_amount = $amount;
                break;
            case 5:
                $discount_amount = $amount;
                break;
            case 6:
                $discount_amount = $amount;
                break;
            case 7:
                $discount_amount = $amount;
                break;
            default:
                $discount_amount = $amount;
                break;
        }
        return $discount_amount;
    }

    /**
     * 使用分页的时候，去除多余的字段
     * @param $list
     * @return mixed
     */
    public function removePagingField($list){
        unset($list['first_page_url'], $list['from'],
              $list['from'], $list['last_page_url'],
              $list['next_page_url'], $list['path'],
              $list['prev_page_url'], $list['to']);
        return $list;
    }

    /**
     * 加工房产地址，将地区码转换成详细地址，并获取经纬度
     * @param $codes
     * @param $append
     * @param null $level
     * @param bool $after   为true表示该等级以下地区
     * @return mixed
     */
    protected function  makeAddress($codes, $append, $level = null,$after = false){
        $codes      = trim($codes,',');
        $area_codes = explode(',',$codes);
        $where      = ['code' => ['in',$area_codes]];
        if (!empty($level)){
            if ($after){
                $where['level'] = ['>',$level];
            }else{
                $where['level'] = $level;
            }
        }
        $area_list  = CommonAreaRepository::getList($where,['code','name','lng','lat']);
        $area_address = '';
        foreach ($area_codes as $code){
            if ($area = $this->searchArray($area_list,'code',$code)){
                $area_address .= reset($area)['name'];
            }
        }
        $area_address .= $append;
        $lng = '';
        $lat = '';
        if ($area_l_l = $this->searchArray($area_list,'code',end($area_codes))){
            $lng = reset($area_l_l)['lng'];
            $lat = reset($area_l_l)['lat'];
        }
        return [$area_address,$lng,$lat];
    }

    /**
     * 计算二维数组中指定键的和
     * @param $array
     * @param $field
     * @return int|mixed
     */
    protected function arrayFieldSum($array, $field){
        if (empty($array)){
            return 0;
        }
        $sum = 0;
        foreach ($array as $value){
            if (isset($value[$field])){
                if (is_integer($value[$field]))
                $sum += $value[$field];
            }
        }
        return $sum;
    }

    protected function getArrayIds(array $list,array $column){
        if (empty($list) || empty($column)){
            return [];
        }
        foreach ($list as &$value){
            foreach ($column as $item){
                if (!isset($value[$item])){
                    return false;
                }
                $item_arr = array_column($list,$item);
                foreach ($v_arr as $i){
                    if (!empty($v)){
                        $str_role_ids .= trim($i,',').',';
                    }
                }
            }
        }
        foreach ($column as $v){
            $v_arr = array_column($list,$v);
            foreach ($v_arr as $i){
                if (!empty($v)){
                    $str_role_ids .= trim($i,',').',';
                }
            }
        }
    }
}