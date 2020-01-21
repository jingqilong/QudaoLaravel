<?php


namespace App\Repositories;


use App\Models\ShopGoodsSpecRelateModel;
use App\Repositories\Traits\RepositoryTrait;
use App\Services\Common\ImagesService;
use Illuminate\Support\Arr;

class ShopGoodsSpecRelateRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopGoodsSpecRelateModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取规格的库存数量 没有规格返回传进来的参数
     * @param $goods_id
     * @param $stock
     * @return float|int
     */
    protected function getStockCount($goods_id, $stock)
    {
        if ($spec_stock_arr = $this->getAllList(['goods_id' => $goods_id,'deleted_at' => 0],['stock'])){
            return array_sum(Arr::flatten($spec_stock_arr));
        }
        return $stock;
    }

    /**
     * 获取拼装成字符串的规格列表
     * @param $spec_relate_ids
     * @return array
     */
    protected function getStrSpecList($spec_relate_ids){
        if (empty($spec_relate_ids)){
            return [];
        }
        if (!$spec_relate_list = $this->getAllList(['id' => ['in',$spec_relate_ids],'deleted_at' => 0])){
            return [];
        }
        $spec_ids  = array_column($spec_relate_list,'spec_ids');
        foreach ($spec_ids as &$v){$v = trim($v,',');}
        $spec_ids  = implode(',',$spec_ids);
        $spec_list = ShopGoodsSpecRepository::getAssignList(explode(',',$spec_ids),['id','spec_value','spec_name']);
        $spec_list = $this->createArrayIndex($spec_list,'id');
        $str_spec_list = [];
        foreach ($spec_relate_list as $spec_relate){
            $relate_spec_ids = explode(',',trim($spec_relate['spec_ids'],','));
            $str_spec = '';
            foreach ($relate_spec_ids as $id){
                if (!isset($spec_list[$id]))continue;
                $str_spec .= $spec_list[$id]['spec_name'] .':'. $spec_list[$id]['spec_value'] . ';';
            }
            $str_spec_list[$spec_relate['id']] = $str_spec;
        }
        return $str_spec_list;
    }
}
            