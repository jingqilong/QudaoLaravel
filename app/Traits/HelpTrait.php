<?php


namespace App\Traits;


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
}