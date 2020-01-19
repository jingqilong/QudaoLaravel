<?php


namespace App\Repositories;


use App\Enums\CollectTypeEnum;
use App\Enums\ShopGoodsEnum;
use App\Models\ShopGoodsModel;
use App\Repositories\Traits\RepositoryTrait;
use App\Services\Common\ImagesService;
use App\Traits\HelpTrait;

class ShopGoodsRepository extends ApiRepository
{
    use RepositoryTrait;
    use HelpTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopGoodsModel $model)
    {
        $this->model = $model;
    }


    /**
     * 随机获取推荐的商品
     * @param int $count
     * @param array $column
     * @param array $where
     * @return array|null
     */
    protected function getShopRandomCount(int $count, array $column, array $where)
    {
        if (!$list = $this->getRandomCount($count,$column,$where)){
            return [];
        }
        foreach ($list as &$value){
            $value['price']  = '￥'.sprintf('%.2f',round($value['price'] / 100, 2));
            $value['labels'] = empty($value['labels']) ? [] : explode(',',trim($value['labels'],','));
        }
        return $list;
    }


    /**
     * 获取收藏列表
     * @param array $request
     * @return array|mixed|null
     */
    protected function getCollectList($request)
    {
        $column = ['id', 'name','negotiable', 'category', 'banner_ids', 'price', 'score_deduction', 'score_categories'];
        if (!$list = $this->getList(['id' => ['in', $request['collect_ids']], 'deleted_at' => 0], $column,'id','desc')) {
            return [];
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            return $list;
        }
        $list['data'] = ImagesService::getListImages($list['data'], ['banner_ids' => 'single']);
        foreach ($list['data'] as &$value) {
            if (ShopGoodsEnum::NEGOTIABLE == $value['negotiable']){
                $value['price'] = '面议';
            }else{
                $value['price']     = empty($value['price']) ? '0.00' : sprintf('%.2f',round($value['price'] / 100,2));
            }
            $value['type']      = $request['type'];
            $value['type_name'] = CollectTypeEnum::getType($request['type'],'');
            unset($value['banner_ids'],$value['score_categories'],$value['category'],$value['negotiable']);
        }
        return $list;
    }

    /**
     * 未髮現有使用的地方
     * @param array $common_ids
     * @return array|mixed|null
     * @deprecated true
     */
    protected function getCommonList(array $common_ids)
    {
        $column = ['id', 'name', 'category', 'banner_ids', 'price', 'score_deduction', 'score_categories'];
        $this->setPerPage(999);
        if (!$list = $this->getList(['id' => ['in', $common_ids], 'deleted_at' => 0], $column,'id','desc')) {
            return [];
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            return $list;
        }
        $list = ImagesService::getListImages($list['data'], ['banner_ids' => 'single']);
        foreach ($list as &$value) {
            unset($value['banner_ids'],$value['score_categories'],$value['category']);
        }
        return $list;
    }

}
            