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
}
            