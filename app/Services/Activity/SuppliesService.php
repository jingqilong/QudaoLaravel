<?php
namespace App\Services\Activity;


use App\Enums\ActivityEnum;
use App\Repositories\ActivityDetailRepository;
use App\Repositories\ActivitySuppliesParameterRepository;
use App\Repositories\ActivitySuppliesRepository;
use App\Repositories\ActivityThemeRepository;
use App\Repositories\CommonImagesRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class SuppliesService extends BaseService
{

    /**
     * 添加活动用品
     * @param $request
     * @return bool
     */
    public function addSupplies($request)
    {
        if (!ActivityThemeRepository::exists(['id' => $request['theme_id']])){
            $this->setError('主题不存在！');
            return false;
        }
        $add_arr = [
            'name'          => $request['name'],
            'price'         => isset($request['price']) ? $request['price'] * 100 : 0,
            'is_recommend'  => $request['is_recommend'] ?? 0,
            'link'          => $request['link'] ?? '',
            'detail'        => $request['detail'],
            'image_ids'     => $request['image_ids'],
            'source'        => $request['source'],
            'theme_id'      => $request['theme_id'],
        ];

        if (ActivitySuppliesRepository::exists($add_arr)){
            $this->setError('该用品已添加！');
            return false;
        }
        $add_arr['created_at'] = time();
        $add_arr['updated_at'] = time();
        DB::beginTransaction();
        if (!$id = ActivitySuppliesRepository::getAddId($add_arr)){
            $this->setError('添加失败！');
            DB::rollBack();
            return false;
        }
        if (isset($request['parameter'])){
            $parameter = json_decode($request['parameter']);
            $para_arr  = [];
            foreach ($parameter as $k => $value){
                $para_arr[] = [
                    'supplies_id'   => $id,
                    'key'           => $k,
                    'value'         => $value,
                    'created_at'    => time(),
                    'updated_at'    => time(),
                ];
            }
            if (!ActivitySuppliesParameterRepository::create($para_arr)){
                $this->setError('添加失败！');
                DB::rollBack();
                return false;
            }
        }
        DB::commit();
        $this->setMessage('添加成功！');
        return true;
    }

    /**
     * 删除用品
     * @param $id
     * @return bool
     */
    public function deleteSupplies($id)
    {
        if (!ActivitySuppliesRepository::exists(['id' => $id])){
            $this->setError('用品不存在！');
            return false;
        }
        if (ActivityDetailRepository::exists(['supplies_ids' => ['like','%'.$id.',%']])){
            $this->setError('该用品已使用，无法删除，只能修改！');
            return false;
        }
        if (ActivitySuppliesRepository::delete(['id' => $id])){
            $this->setMessage('删除成功！');
            return true;
        }
        ActivitySuppliesParameterRepository::delete(['supplies_id' => $id]);
        $this->setError('删除失败！');
        return false;
    }


    /**
     * 修改活动用品
     * @param $request
     * @return bool
     */
    public function editSupplies($request)
    {
        if (!ActivitySuppliesRepository::exists(['id' => $request['id']])){
            $this->setError('用品不存在！');
            return false;
        }
        if (!ActivityThemeRepository::exists(['id' => $request['theme_id']])){
            $this->setError('主题不存在！');
            return false;
        }
        $upd_arr = [
            'name'          => $request['name'],
            'price'         => isset($request['price']) ? $request['price'] * 100 : 0,
            'is_recommend'  => $request['is_recommend'] ?? 0,
            'link'          => $request['link'] ?? '',
            'detail'        => $request['detail'],
            'image_ids'     => $request['image_ids'],
            'source'        => $request['source'],
            'theme_id'      => $request['theme_id'],
        ];

        if (ActivitySuppliesRepository::exists(array_merge($upd_arr,['id' => ['<>',$request['id']]]))){
            $this->setError('用品信息重复！');
            return false;
        }
        $add_arr['created_at'] = time();
        $add_arr['updated_at'] = time();
        DB::beginTransaction();
        if (!$id = ActivitySuppliesRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setError('修改失败！');
            DB::rollBack();
            return false;
        }
        ActivitySuppliesParameterRepository::delete(['supplies_id' => $id]);
        if (isset($request['parameter'])){
            $parameter = json_decode($request['parameter']);
            $para_arr  = [];
            foreach ($parameter as $k => $value){
                $para_arr[] = [
                    'supplies_id'   => $id,
                    'key'           => $k,
                    'value'         => $value,
                    'created_at'    => time(),
                    'updated_at'    => time(),
                ];
            }
            if (!ActivitySuppliesParameterRepository::create($para_arr)){
                $this->setError('添加失败！');
                DB::rollBack();
                return false;
            }
        }
        DB::commit();
        $this->setMessage('修改成功！');
        return true;
    }

    /**
     * 获取场景列表
     * @param $page
     * @param $page_num
     * @return bool|null
     */
    public function getSuppliesList($page, $page_num){
        if (!$list = ActivitySuppliesRepository::getList(['id' => ['>',0]],['*'],'id','asc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        unset($list['first_page_url'], $list['from'],
            $list['from'], $list['last_page_url'],
            $list['next_page_url'], $list['path'],
            $list['prev_page_url'], $list['to']);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        foreach ($list['data'] as &$value){
            $value['price']      = empty($value['price']) ? 0 : round($value['price'] / 100,2);
            $value['theme']      = ActivityThemeRepository::getField(['id' => $value['theme_id']],'name');
            $value['images']     = [];
            if (!empty($value['images_ids'])){
                $image_ids = explode(',',$value['images_ids']);
                if ($image_list = CommonImagesRepository::getList(['id' => ['in', $image_ids]],['img_url'])){
                    $image_list     = array_column($image_list,'img_url');
                    $value['images']= $image_list;
                }
            }
            $value['parameter'] = [];
            if ($parameter = ActivitySuppliesParameterRepository::getList(['supplies_id' => $value['id']],['key','value'])){
                $value['parameter'] = $parameter;
            }
            $value['source']        = ActivityEnum::getStatus($value['source']);
            $value['created_at']    = date('Y-m-d H:m:i',$value['created_at']);
            $value['updated_at']    = date('Y-m-d H:m:i',$value['updated_at']);
            unset($value['theme_id'],$value['images_ids']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            