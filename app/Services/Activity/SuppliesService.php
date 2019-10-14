<?php
namespace App\Services\Activity;


use App\Repositories\ActivitySuppliesRepository;
use App\Repositories\ActivityThemeRepository;
use App\Services\BaseService;

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
        if (ActivitySuppliesRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }
}
            