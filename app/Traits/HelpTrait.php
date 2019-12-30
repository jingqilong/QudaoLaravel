<?php


namespace App\Traits;


use App\Enums\ImageTypeEnum;
use App\Repositories\CommonAreaRepository;
use Illuminate\Support\Arr;

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
     * 数组中查询指定范围的数据
     * @param $array
     * @param $filed
     * @param $range
     * @return array|bool
     */
    function searchRangeArray(array $array,string $filed,array $range){
        if (!in_array($filed,array_keys(reset($array)))){
            return false;
        }
        $res = [];
        foreach ($array as $k => $v){
            if (!is_array($v)){
                continue;
            }
            if ($v[$filed] > reset($range) && $v[$filed] < end($range)){
                $res[] = $v;
            }

        }
        return $res;
    }

    /**
     * 数组中模糊查询指定键值的数据
     * @param $array
     * @param $filed
     * @param $value
     * @return array|bool
     */
    function likeSearchArray($array, $filed, $value){
        $res = [];
        foreach ($array as $k => $v){
            if (is_array($v) && (strpos($v[$filed],$value) !== false)){
                $res[] = $v;continue;
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
     * @desc 将地区码转换成地区名字符串
     * @param $codes   ,行政区划编码
     * @param int $from_level  ,从哪个级别开始获取
     * @return string
     */
    public function getAreaName($codes,$from_level = 0){
        $codes      = trim($codes,',');
        $area_codes = explode(',',$codes);
        $where      = ['code' => ['in',$area_codes]];
        if (!empty($from_level)){
            if ($from_level){
                $where['level'] = ['>',$from_level];
            }else{
                $where['level'] = $from_level;
            }
        }
        $area_list  = CommonAreaRepository::getList($where,['code','name']);
        $area_address = '';
        foreach ($area_codes as $code){
            if ($area = $this->searchArray($area_list,'code',$code)){
                $area_address .= reset($area)['name'];
            }
        }
        return $area_address;
    }

    /**
     * 加工房产地址，将地区码转换成详细地址，并获取经纬度
     * @param $codes
     * @param $append
     * @param null $level
     * @param bool $after   为true表示该等级以下地区
     * @return mixed
     * @deprecated true
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

    /**
     * 获取列表中id串等的数组
     * @param array $list
     * @param array $column
     * @return array|bool
     */
    protected function getArrayIds(array $list, array $column){
        if (empty($list) || empty($column)){
            return [];
        }
        foreach ($list as &$value){
            foreach ($column as $item){
                if (!isset($value[$item])){
                    return false;
                }
            }
        }
        $res = [];
        foreach ($column as $k => $v){
            $res[$k] = [];
            $item_arr = array_unique(array_column($list,$v));
            $strs = '';
            foreach ($item_arr as $i){
                if (!empty($i)){
                    $strs .= trim($i,',').',';
                }
            }
            if (!empty($strs)){
                $res[$k] = explode(',',trim($strs,','));
            }
        }
        return $res;
    }

    /**
     * 生成随机码
     * @param int $len
     * @return string
     */
    protected function getSignCode($len = 10){
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        mt_srand(10000000*(double)microtime());
        for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, $lc)];
        }
        return $str;
    }

    /**
     * @param $image   [需要加后缀的图片]
     * @param $size    [ 1 => 375尺寸 NORMAL,2 => 200尺寸 small]
     * @return string
     */
    public function suffix($image, $size)
    {
        if (!is_array($image)){
            $image = $image . ImageTypeEnum::getSize($size);
        }else foreach ($image as &$value)$value = $value . ImageTypeEnum::getSize($size);
        return $image;
    }


    /**
     * 根据出生日期计算年龄、生肖、星座
     * @param string $birthday = "2018-10-23" 日期
     * @param string $symbol 符号
     * @return $array
     * */
    public function birthday($birthday,$symbol='-'){

        //计算年龄
        $birth = date('Y-m-d',strtotime($birthday));
        list($by,$bm,$bd)=explode($symbol,$birth);
        $cm=date('n');
        $cd=date('j');
        $age=date('Y')-$by-1;
        if ($cm>$bm || $cm==$bm && $cd>$bd) $age++;
        $array['age'] = $age;

        //计算生肖
        $animals = array(
            '鼠', '牛', '虎', '兔', '龙', '蛇',
            '马', '羊', '猴', '鸡', '狗', '猪'
        );
        $key = ($by - 1900) % 12;
        $array['animals'] = $animals[$key];

        //计算星座
        $constellation_name = array(
            '水瓶座','双鱼座','白羊座','金牛座','双子座','巨蟹座',
            '狮子座','处女座','天秤座','天蝎座','射手座','摩羯座'
        );
        if ($bd <= 22){
            if ('1' !== $bm) $constellation = $constellation_name[$bm-2]; else $constellation = $constellation_name[11];
        }else $constellation = $constellation_name[$bm-1];
        $array['constellation'] = $constellation;

        return $array;
    }
}