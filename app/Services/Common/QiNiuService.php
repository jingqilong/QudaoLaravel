<?php


namespace App\Services\Common;


use App\Enums\CommonImagesEnum;
use App\Enums\ImageTypeEnum;
use App\Enums\QiNiuEnum;
use App\Repositories\CommonImagesRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use App\Traits\QiNiuTrait;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Tolawho\Loggy\Facades\Loggy;
use zgldh\QiniuStorage\QiniuStorage;

class QiNiuService extends BaseService
{
    use HelpTrait,QiNiuTrait;


    public function migrationFile(){
        /**
         * 1，读取所有文件
         * 2，读取数据库中图片相关表的信息
         * 3，根据模块生成七牛云配置
         * 4，生成七牛云对象，将图片上传至七牛云
         * 5，将七牛云返回的图片地址存入对应的数据表
         * 6，完成文件迁移
         */
        #测试
//        $path = 'C:\\phpStudy\\PHPTutorial\\WWW\\qudao_laravel\\public\\upload\\images\\Activity\\2018-11-21\\gsnh.png';
//        $url = $this->uploadQiniu($this->module_config['Activity'],'gsnh.png', $path);
//        dd($url);

        $path = public_path('upload\images');
        $file_list = $this->traverseFile($path);
        $file_list['ActivityTemp'] = $file_list['Activity'];//dd($file_list);
        $upload_data = [];
        foreach ($this->correspond_arr as $module => $table){
            //此处按模块进行上传，避免访问超时，Activity、ActivityTemp、Estate、Goods、Member、Project
            if ($module != 'Project'){
                continue;
            }
            $model = $this->getModel($table);
            $all_data = $model->get($this->columns[$table]);//dd($all_data);
            if (empty($all_data)){
                continue;
            }
            foreach ($all_data as $key => $value){
                $where = [];
                $primary_key_name = reset($this->columns[$table]);
                $primary_key    = $value->{$primary_key_name};
                $where = [$primary_key_name => $primary_key];//条件
                $upd_data = [];//要跟新的数据
                $img_fields = $this->img_field[$table];//每张表中要上传的字段
                foreach ($img_fields as $image_field => $path){
                    $str = $value->{$path};
                    if (strpos($str, $module)){
                        $str = strstr($str,$module);
                    }
                    if ($check_mark = strpos($str, '?')){
                        $str = substr($str,0,$check_mark);
                    }
                    $img_arr = explode('/',$str);
                    if (in_array($module,$img_arr))
                        unset($img_arr[0]);
                    //dd($img_arr);
                    if (!isset($img_arr[1])){
                        $upd_data[$image_field] = 0;
                        if (isset($upload_data[$module][$primary_key])){
                            $upload_data[$module][$primary_key] += [$image_field => 0];
                        }else{
                            $upload_data[$module][$primary_key] = [
                                $primary_key_name => $primary_key,
                                $image_field => 0
                            ];
                        }
                        continue;
                    }
                    //上传路径
                    $up_path = Arr::get($file_list[$module],reset($img_arr));
                    //本地路径
                    $local_path = Arr::get($up_path,end($img_arr));//dd($local_path);
                    if (empty($local_path)){
                        $upd_data[$image_field] = 0;
                        if (isset($upload_data[$module][$primary_key])){
                            $upload_data[$module][$primary_key] += [$image_field => 0];
                        }else{
                            $upload_data[$module][$primary_key] = [
                                $primary_key_name => $primary_key,
                                $image_field => 0
                            ];
                        }
                        continue;
                    }
                    //$path = 'C:\\phpStudy\\PHPTutorial\\WWW\\qudao_laravel\\public\\upload\\images\\Activity\\2018-11-21\\gsnh.png';
                    $id = $this->uploadQiniu($module,$img_arr[1], $local_path);
                    $upd_data[$image_field] = $id;
                    if (isset($upload_data[$module][$primary_key])){
                        $upload_data[$module][$primary_key] += [$image_field => $id];
                    }else{
                        $upload_data[$module][$primary_key] = [
                            $primary_key_name => $primary_key,
                            $image_field => $id
                        ];
                    }
                }
                if (!$this->update($this->getModel($table),$where,$upd_data)){
                    Loggy::write('error','信息更新失败，更新信息：'.json_encode($upd_data).'  条件：'.json_encode($where).' 类型：'.$module);
                }
            }
        }//dd($upload_data);
        return $upload_data;
    }


    /**
     * 获取旧数据库中的表模型
     * @param string $table     表名
     * @return Builder
     */
    public function getModel(string $table){
        //$connection = DB::connection('local_taiji');//本地
        $connection = DB::connection('online_taiji');//线上
        $model = $connection->table($table);
        return $model;
    }


    /**
     * 更新数据
     * @param Builder $model
     * @param $where
     * @param $data
     * @return bool
     */
    public function update(Builder $model, $where, $data){
        if ($model->where($where)->update($data)){
            return true;
        }
        return false;
    }

    /**
     * 根据不同的配置迁移图片至七牛云（迁移用）
     * @param $module
     * @param $name
     * @param $path
     * @return bool|string  返回图片上传后存入数据表中的id
     */
    public function uploadQiniu($module, $name, $path){
//        $config = $this->module_config_test[$module];//测试
        $config = $this->module_config[$module];
        $model = $this->getModel('my_images');
        //获取七牛云配置
        config([
            'filesystems.disks.qiniu.bucket' => $config['bucket'],
            'filesystems.disks.qiniu.domains' => $config['domains']
        ]);
        //$config = config('filesystems.disks.qiniu');
        $disk = QiniuStorage::disk('qiniu');
        $fileName = $config['bucket'].'/'.md5($name.$path).'.'.$name;
        if ($disk->exists($fileName)){//如果图片已经上传
            $url = (string)$disk->downloadUrl($fileName);
            $img_info = $model->where(['img_url' => $url])->first(['id']);
            if ($img_info){
                //如果数据库中存在，返回id，如果不存在，在下面插入数据库
                return $img_info->id;
            }
        }else{//如果图片没有上传
            $res = $disk->put($fileName, file_get_contents($path));
            if (!$res){//上传失败
                Loggy::write('error','图片上传七牛云失败，图片名称：'.$name.'  本地地址：'.$path.' 类型：'.$module);
                return 0;
            }
            $url = (string)$disk->downloadUrl($fileName);
        }
        if (!$id = $this->getModel('my_images')->insertGetId([
            'type' => ImageTypeEnum::getConst(strtoupper($module)),
            'img_url' => $url,
            'create_at' => time()
        ])){
            Loggy::write('error','图片添加失败，图片名称：'.$name.'  七牛云地址：'.$url.' 类型：'.$module);
            return 0;
        }
        return $id;
    }

    /**
     * 上传图片至七牛云，（上传新的图片，不是迁移）
     * @param $storage_space
     * @return array
     */
    public function upload($storage_space)
    {
        if (!QiNiuEnum::exists($storage_space)){
            return ['code' => 0, 'message' => '存储空间类别不存在'];
        }
        if (!$files = request()->file()){
            return ['code' => 0, 'message' => '请传入要上传的图片或视频'];
        }
        if (count($files) > 20){
            return ['code' => 0, 'message' => '单次上传不能超过20个'];
        }
        //对传入的文件进行预检
        foreach ($files as $info){
            $file_name = $info->getClientOriginalName();
            if (preg_match("/[()（）?]/",$file_name)){
                return ['code' => 0, 'message' => '图片名字中不能包含特殊字符【中英文括号、问号】'];
            }
            $file_type = $info->getMimeType();
            $temp = explode('/',$file_type);
            $file_format = reset($temp);
            if ($file_format !== 'image'){
                return ['code' => 0, 'message' => '文件['.$file_name.']不是图片，无法上传！'];
            }
            if (!$info->isValid()){
                return ['code' => 0, 'message' => '文件['.$file_name.']上传过程出错！'];
            }
        }
        //上传图片至七牛云
        $config = $this->upload_config[QiNiuEnum::$module[$storage_space]];
        config([
            'filesystems.disks.qiniu.bucket' => $config['bucket'],
            'filesystems.disks.qiniu.domains' => $config['domains']
        ]);
        $disk = QiniuStorage::disk('qiniu');
        $result = [];
        $count = 0;
        foreach ($files as $info){
            $name = $info->getClientOriginalName();
            $path = $info->getRealPath();
            $file_name = $config['bucket'].'/'.$name;
            $result[$name]['name'] = $file_name;
            if ($disk->exists($file_name)){//如果图片已经上传
                $url = (string)$disk->downloadUrl($file_name);
                $img_info = CommonImagesRepository::getOne(['img_url' => $url]);
                $result[$name]['id'] = $img_info['id'];
                $result[$name]['url'] = $url;
                continue;
            }
            $res = $disk->put($file_name, file_get_contents($path));
            $url = '';
            if (!$res){
                Loggy::write('error','图片上传七牛云失败，图片名称：'.$name.'  本地地址：'.$path.' 类型：'.QiNiuEnum::$module[$storage_space]);
                $result[$name]['id'] = 0;
                $result[$name]['url'] = $url;
                continue;
            }
            $url = (string)$disk->downloadUrl($file_name);
            if (!$id = CommonImagesRepository::getAddId([
                'type' => $storage_space,
                'img_url' => $url.'',
                'create_at' => time()
            ])){
                Loggy::write('error','图片添加失败，图片名称：'.$name.'  七牛云地址：'.$url.' 类型：'.QiNiuEnum::$module[$storage_space]);
                $result[$name]['id'] = 0;
                $result[$name]['url'] = '';
                continue;
            }
            $count++;
            $result[$name]['id'] = $id;
            $result[$name]['url'] = $url;
        }
        return ['code' => 1, 'message' => '总共'.$count.'张图片上传成功！', 'data' => $result];
    }


    /**
     * 上传切割图至七牛云并更新正式服中的数据
     * @return array
     */
    public function migrationBigImage(){
        /**
         * 1，读取所有文件
         * 2，读取数据库中图片相关表的信息
         * 3，根据模块生成七牛云配置
         * 4，生成七牛云对象，将图片上传至七牛云
         * 5，将七牛云返回的图片地址存入对应的数据表
         * 6，完成文件迁移
         */
        #测试
//        $path = 'C:\\phpStudy\\PHPTutorial\\WWW\\qudao_laravel\\public\\upload\\images\\Activity\\2018-11-21\\gsnh.png';
//        $url = $this->uploadQiniu($this->module_config['Activity'],'gsnh.png', $path);
//        dd($url);

        $path = public_path('upload');
        $file_list = $this->traverseFile($path);
        $upload_data = [];
        $correspond_arr = [
            'event' => 'my_hd_activity',
            'shop'  => 'my_sc_goods_common'
        ];
        $module_file = [
            'event' => 'Activity',
            'shop'  => 'Goods'
        ];
        $img_field = [
            'my_hd_activity'    => 'a_img_ids',
            'my_sc_goods_common' => 'desc_img_ids'
        ];
        $id_field = [
            'my_hd_activity'    => 'a_id',
            'my_sc_goods_common' => 'goods_common_id'
        ];
        foreach ($correspond_arr as $module => $table){
            //此处按模块进行上传，避免访问超时，event、shop
            if ($module != 'shop'){
                continue;
            }
            foreach ($file_list[$module] as $id => $images){
                $ids = '';
                $upload_data[$module][$id] = [];
                foreach ($images as $name => $local_path){
                    $qiniu_id = $this->uploadQiniu($module_file[$module],$name, $local_path);
                    $ids .= $qiniu_id.',';
                    $upload_data[$module][$id][$name] = $qiniu_id;
                }
                $ids = trim($ids,',');
                $upload_data[$module][$id]['ids'] = $ids;
                //dd($upload_data[$module][$id]);
                $where = [$id_field[$table] => $id];
                $upd_data = [$img_field[$table] => $ids];
                if (!$this->update($this->getModel($table), $where, $upd_data)){
                    Loggy::write('error','信息更新失败，更新信息：'.json_encode($upd_data).'  条件：'.json_encode($where).' 类型：'.$module);
                }
            }
        }
        return $upload_data;
    }


    /**
     * 根据不同的配置上传图片至七牛云
     * @param $module
     * @param $name
     * @param $path
     * @return mixed  返回图片上传后存入数据表中的id
     */
    public function uploadImages($module, $name, $path){
//        $config = $this->module_config_test[$module];//测试
        $config = $this->module_config[$module];
        //获取七牛云配置
        config([
            'filesystems.disks.qiniu.bucket' => $config['bucket'],
            'filesystems.disks.qiniu.domains' => $config['domains']
        ]);
        //$config = config('filesystems.disks.qiniu');
        $disk = QiniuStorage::disk('qiniu');
        $fileName = $config['bucket'].'/'.md5($name.$path).'.'.$name;
        if ($disk->exists($fileName)){//如果图片已经上传
            $url = (string)$disk->downloadUrl($fileName);
            if ($img_id = CommonImagesRepository::getField(['img_url' => $url],'id')){
                //如果数据库中存在，返回id，如果不存在，在下面插入数据库
                return ['id' => $img_id, 'url' => $url];
            }
        }else{//如果图片没有上传
            $res = $disk->put($fileName, file_get_contents($path));
            if (!$res){//上传失败
                Loggy::write('error','图片上传七牛云失败，图片名称：'.$name.'  本地地址：'.$path.' 类型：'.$module);
                return false;
            }
            $url = (string)$disk->downloadUrl($fileName);
        }
        if (!$id = CommonImagesRepository::getAddId([
            'type' => ImageTypeEnum::getConst(strtoupper($module)),
            'img_url' => $url,
            'create_at' => time()
        ])){
            Loggy::write('error','图片添加失败，图片名称：'.$name.'  七牛云地址：'.$url.' 类型：'.$module);
            return false;
        }
        return ['id' => $id, 'url' => $url];
    }
}