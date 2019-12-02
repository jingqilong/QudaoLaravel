<?php


namespace App\Repositories;


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

    /**$recommend_ids
     * 随机获取推荐的两个商品
     * @param $where
     * @param $column
     * @return |null
     */
    protected function getListToTwo($where, $column)
    {
        if (!$list =$this->getList($where,['id'])){
            return $list;
        }
        $recommend_ids = array_rand($list,2);
        if (count($recommend_ids) != 2){
            return [];
        }
        if (!empty($recommend_ids)){
            $where['id'] = ['in',$recommend_ids];
        }
        if (!$list =$this->getList($where,$column)){
            return $list;
        }
        return $list;
    }

    /**
     * 获取收藏列表
     * @param array $collect_ids
     * @return array|mixed|null
     */
    protected function getCollectList(array $collect_ids)
    {
        $column = ['id', 'name', 'category', 'banner_ids', 'price', 'score_deduction', 'score_categories'];
        if (!$list = $this->getList(['id' => ['in', $collect_ids], 'deleted_at' => 0], $column,'id','desc','1','999')) {
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

    protected function getCommonList(array $common_ids)
    {
        $column = ['id', 'name', 'category', 'banner_ids', 'price', 'score_deduction', 'score_categories'];
        if (!$list = $this->getList(['id' => ['in', $common_ids], 'deleted_at' => 0], $column,'id','desc','1','999')) {
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
            