<?php
namespace App\Services\Activity;


use App\Repositories\ActivityDetailRepository;
use App\Repositories\ActivityLinksRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class LinksService extends BaseService
{

    /**
     * 给活动添加相关链接
     * @param $request
     * @return bool
     */
    public function addLink($request)
    {
        if (!ActivityDetailRepository::exists(['id' => $request['activity_id']])){
            $this->setError('活动不存在！');
            return false;
        }
        $parameter = json_decode($request['parameters'],true);
        $add_arr = [];
        foreach ($parameter as $value){
            if (!isset($value['title'])){
                $this->setError('链接标签不能为空！');
                return false;
            }
            if (!isset($value['url'])){
                $this->setError('链接不能为空！');
                return false;
            }
            if (!empty($value['image_id']) && !is_integer($value['image_id'])){
                $this->setError('链接图ID必须为整数！');
                return false;
            }
            $add_arr[] = [
                'activity_id'   => $request['activity_id'],
                'title'         => $value['title'],
                'url'           => $value['url'],
                'image_id'      => $value['image_id'],
                'created_at'    => time(),
                'updated_at'    => time(),
            ];
        }
        DB::beginTransaction();
        ActivityLinksRepository::delete(['activity_id'   => $request['activity_id']]);
        if (ActivityLinksRepository::exists(['activity_id'   => $request['activity_id']])){
            $this->setError('添加失败！');
            DB::rollBack();
            return false;
        }
        if (!ActivityLinksRepository::create($add_arr)){
            $this->setError('添加失败！');
            DB::rollBack();
            return false;
        }
        $this->setMessage('添加成功！');
        DB::commit();
        return true;
    }
}
            