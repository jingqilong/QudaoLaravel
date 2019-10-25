<?php
namespace App\Services\Common;


use App\Enums\CommonImagesEnum;
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
}
            