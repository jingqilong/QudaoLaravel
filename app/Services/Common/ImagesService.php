<?php
namespace App\Services\Common;


use App\Enums\CommonImagesEnum;
use App\Enums\QiNiuEnum;
use App\Repositories\CommonImagesRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;

class ImagesService extends BaseService
{
    use HelpTrait;

    /**
     * 获取图片仓库
     * @param $request
     * @return bool|mixed|null
     */
    public function getImageRepository($request)
    {
        $order      = $request['order'] ?? 'asc';
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $where      = ['id' => ['>',0]];
        if (isset($request['type'])){
            if (!CommonImagesEnum::isset($request['type'])){
                $this->setError('图片类型不存在！');
                return false;
            }
            $where['type'] = $request['type'];
        }
        if (!$list = CommonImagesRepository::getList($where,['*'],'id',$order,$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setError('暂无数据！');
            return $list;
        }
        foreach ($list['data'] as &$value){
            $value['type_name'] = CommonImagesEnum::getImageType($value['type']);
            $value['create_at'] = date('Y-m-d H:i:s',$value['create_at']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 后台使用
     * 查询数据列表是，帮助获取数据列表中的图片数据
     * @param array $list       数据列表
     * @param array $column     需要查询图片的列，single表示单列图片，several表示图片ID串，格式，['列1' => 'single','列2' => 'several']
     * @return array
     */
    protected function getListImages(array $list, array $column,$merge = false){
        if (empty($list) || empty($column)){
            $this->setError('数据列表或查询列为空！');
            return $list;
        }
        $all_str = '';
        foreach ($column as $key=>$v){
            $image_str = array_column($list,$key);
            $part_str = '';
            foreach ($image_str as $str){
                $part_str .= rtrim($str,',') . ',';
            }
            $all_str .= rtrim($part_str,',') . ',';
        }
        $all_image_ids = array_unique(explode(',',rtrim($all_str,',')));
        $all_image_list = CommonImagesRepository::getList(['id' => ['in',$all_image_ids]],['id','img_url']);

        foreach ($list as &$item){
            foreach ($column as $k=>$v){
                if (array_key_exists($k, $item)){
                    if ($v == 'single'){
                        $ids = explode(',',$item[$k]);
                        $image_column = rtrim(rtrim($k,'_id'),'_ids').'_url';
                        $item[$image_column] = '';
                        if ($image = self::searchArray($all_image_list,'id',reset($ids))){
                            $item[$image_column] = reset($image)['img_url'];
                        }
                    }
                    if ($v == 'several'){
                        $ids = explode(',',$item[$k]);
                        $images_column = rtrim(rtrim($k,'_id'),'_ids').'_urls';
                        $urls = [];
                        foreach ($ids as $id){
                            if ($image = self::searchArray($all_image_list,'id',$id)){
                                if ($merge)
                                    $urls[] = reset($image)['img_url'];
                                else
                                $urls[] = reset($image);
                            }
                        }
                        $item[$images_column] = $urls;
                    }
                }
            }
        }
        $this->setMessage('查询成功！');
        return $list;
    }

    /**
     * 前端使用
     * 查询数据列表是，帮助获取数据列表中的图片数据
     * @param array $list       数据列表
     * @param array $column     需要查询图片的列，single表示单列图片，several表示图片ID串，格式，['列1' => 'single','列2' => 'several']
     * @return array
     */
    protected function getListImagesConcise(array $list, array $column){
        if (empty($list) || empty($column)){
            $this->setError('数据列表或查询列为空！');
            return $list;
        }
        $all_str = '';
        foreach ($column as $key=>$v){
            $image_str = array_column($list,$key);
            $part_str = '';
            foreach ($image_str as $str){
                $part_str .= rtrim($str,',') . ',';
            }
            $all_str .= rtrim($part_str,',') . ',';
        }
        $all_image_ids = array_unique(explode(',',rtrim($all_str,',')));
        $all_image_list = CommonImagesRepository::getList(['id' => ['in',$all_image_ids]],['id','img_url']);

        foreach ($list as &$item){
            foreach ($column as $k=>$v){
                if (array_key_exists($k, $item)){
                    if ($v == 'single'){
                        $ids = explode(',',$item[$k]);
                        $image_column = rtrim(rtrim($k,'_id'),'_ids').'_url';
                        $item[$image_column] = '';
                        if ($image = self::searchArray($all_image_list,'id',reset($ids))){
                            $item[$image_column] = reset($image)['img_url'];
                        }
                    }
                    if ($v == 'several'){
                        $ids = explode(',',$item[$k]);
                        $images_column = rtrim(rtrim($k,'_id'),'_ids').'_urls';
                        $urls = [];
                        foreach ($ids as $id){
                            if ($image = self::searchArray($all_image_list,'id',$id)){
                                $urls[] = reset($image)['img_url'];
                            }
                        }
                        $item[$images_column] = $urls;
                    }
                }
            }
        }
        $this->setMessage('查询成功！');
        return $list;
    }


    /**
     * 查询数据列表是，帮助获取数据列表中的图片数据
     * @param array $info       一条数据
     * @param array $column     需要查询图片的列，single表示单列图片，several表示图片ID串，格式，['列1' => 'single','列2' => 'several']
     * @return array
     */
    protected function getOneImagesConcise(array $info, array $column){
        $all_str = '';
        foreach ($column as $key=>$v){
            $image_str = $info[$key];
            $all_str .= trim($image_str,',') . ',';
        }
        $all_image_ids = array_unique(explode(',',rtrim($all_str,',')));
        $all_image_list = CommonImagesRepository::getList(['id' => ['in',$all_image_ids]],['id','img_url']);
        foreach ($column as $k=>$v){
            if (array_key_exists($k, $info)){
                if ($v == 'single'){
                    $ids = explode(',',$info[$k]);
                    $image_column = rtrim(rtrim($k,'_id'),'_ids').'_url';
                    $info[$image_column] = '';
                    if ($image = self::searchArray($all_image_list,'id',reset($ids))){
                        $info[$image_column] = reset($image)['img_url'];
                    }
                }
                if ($v == 'several'){
                    $ids = explode(',',$info[$k]);
                    $images_column = rtrim(rtrim($k,'_id'),'_ids').'_urls';
                    $urls = [];
                    foreach ($ids as $id){
                        if ($image = self::searchArray($all_image_list,'id',$id)){
                            $urls[] = reset($image)['img_url'];
                        }
                    }
                    $info[$images_column] = $urls;
                }
            }
        }
        $this->setMessage('查询成功！');
        return $info;
    }


    /**
     * 添加资源
     * @param $request
     * @return bool|null
     */
    public function addResource($request){
        if (!QiNiuEnum::exists($request['storage_space'])){
            $this->setError('存储空间类别不存在!');
            return false;
        }
        if ($resource = CommonImagesRepository::getOne(['img_url' => $request['url']])){
            $this->setMessage('该资源已存在！');
            return $resource['id'];
        }
        $add_arr = [
            'type'      => $request['storage_space'],
            'img_url'   => $request['url'],
            'create_at' => time()
        ];
        if ($id = CommonImagesRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return $id;
        }
        $this->setError('添加失败！');
        return false;
    }
}
            