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
}