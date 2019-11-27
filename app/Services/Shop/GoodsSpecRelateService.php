<?php
namespace App\Services\Shop;


use App\Repositories\ShopCartRepository;
use App\Repositories\ShopGoodsRepository;
use App\Repositories\ShopGoodsSpecRelateRepository;
use App\Repositories\ShopGoodsSpecRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\DB;

class GoodsSpecRelateService extends BaseService
{
    use HelpTrait;

    /**
     * 获取列表商品公共信息
     * @param $goods_spec_arr
     * @param array $goods_column
     * @return array|bool
     */
    protected function getListCommonInfo($goods_spec_arr, $goods_column=['id','name','price','banner_ids','score_deduction']){
        foreach ($goods_spec_arr as $value){
            if (!isset($value['goods_id']) || !isset($value['number'])){
                $this->setError('商品ID和数量不能为空！');
                return false;
            }
        }
        $spec_relate_ids    = array_column($goods_spec_arr,'spec_relate_id');
        $spec_relate_list   = empty($spec_relate_ids) ? [] : ShopGoodsSpecRelateRepository::getList(['id' => ['in',$spec_relate_ids],'deleted_at' => 0]);
        $goods_ids          = array_column($goods_spec_arr,'goods_id');
        $goods_list         = ShopGoodsRepository::getAssignList($goods_ids,$goods_column);
        $goods_number       = ShopCartRepository::getList(['id' => ['in',$goods_ids]],['number'],'id','desc');
        $goods_list         = ImagesService::getListImages($goods_list,['banner_ids' => 'single']);
        $spec_ids           = implode(',',array_column($spec_relate_list,'spec_ids'));
        $spec_list          = ShopGoodsSpecRepository::getAssignList(explode(',',$spec_ids),['id','spec_value','spec_name']);
        $result             = [];
        foreach ($goods_spec_arr as $key => $value){
            if ($goods  = $this->searchArray($goods_list,'id',$value['goods_id'])){
                $price  =  reset($goods)['price'];
                $result[$key] = [
                    'goods_name'      => reset($goods)['name'],
                    'goods_price'     => sprintf('%.2f',round($price / 100,2)),
                    'main_img_url'    => reset($goods)['banner_url'],
                ];
            }
            $spec_str       = '件';
            if (isset($value['spec_relate_id'])){
                $spec_str = '';
                if ($spec_relate = $this->searchArray($spec_relate_list,'id',$value['spec_relate_id'])){
                    $value_spec_ids = explode(',',trim(reset($spec_relate)['spec_ids'],','));
                    foreach ($value_spec_ids as $value_spec_id){
                        if ($item_spec  = $this->searchArray($spec_list,'id',$value_spec_id)){
                            $spec_str  .= reset($item_spec)['spec_name'] .':'. reset($item_spec)['spec_value'] . ';';
                        }
                    }
                }
            }
            foreach ($goods_list as $k => $v){
                $result[$k]['score_deduction']  = $v['score_deduction'];
            }
            foreach ($goods_number as $k => $v){
                $result[$k]['number'] = $v['number'];
            }
            if (isset($value['order_relate_id'])){
                $result[$key]['order_relate_id'] = $value['order_relate_id'];
            }
            if (isset($value['cart_id'])){
                $result[$key]['cart_id']       = $value['cart_id'];
            }
            $result[$key]['goods_id']           = $value['goods_id'];
            $result[$key]['spec_relate_id']     = isset($value['spec_relate_id']) ? $value['spec_relate_id'] : 0;
            $result[$key]['spec']               = $spec_str;
        }
        $this->setMessage('获取成功!');
        return $result;
    }

    /**
     * 检查库存
     * @param $goods_spec_arr
     * @return bool
     */
    public function checkStock($goods_spec_arr){
        foreach ($goods_spec_arr as $value){
            if (!isset($value['goods_id']) || !isset($value['number'])){
                $this->setError('商品ID和数量不能为空！');
                return false;
            }
        }
        $spec_relate_ids    = array_column($goods_spec_arr,'spec_relate_id');
        $spec_relate_list   = empty($spec_relate_ids) ? [] : ShopGoodsSpecRelateRepository::getList(['id' => ['in',$spec_relate_ids]]);
        $goods_ids          = array_column($goods_spec_arr,'goods_id');
        $goods_list         = ShopGoodsRepository::getAssignList($goods_ids);
        foreach ($goods_spec_arr as $key => $value){
            $goods = $this->searchArray($goods_list,'id',$value['goods_id']);
            if (!isset($value['spec_relate_id'])){
                if ($value['number'] > reset($goods)['stock']){
                    $this->setError('商品【'.reset($goods)['name'].'】库存不足！');
                    return false;
                }
                break;
            }
            if ($spec_relate = $this->searchArray($spec_relate_list,'id',$value['spec_relate_id'])){
                if ($value['number'] > reset($spec_relate)['stock']){
                    $this->setError('商品【'.reset($goods)['name'].'】库存不足！');
                    return false;
                }
            }
        }
        $this->setMessage('库存充盈！');
        return true;
    }

    /**
     * 变更库存
     * @param $goods_spec_arr
     * @param string $option    + or -
     * @return bool
     */
    public function updStock($goods_spec_arr,$option = '-'){
        foreach ($goods_spec_arr as $value){
            if (!isset($value['goods_id']) || !isset($value['number'])){
                $this->setError('商品ID和数量不能为空！');
                return false;
            }
        }
        DB::beginTransaction();
        foreach ($goods_spec_arr as $key => $value){
            if (!isset($value['spec_relate_id'])){
                if (!ShopGoodsRepository::decrement(['id' => $value['goods_id']],'stock',$option.$value['number'])){
                    $this->setError('扣除库存失败！');
                    DB::rollBack();
                    return false;
                }
                break;
            }
            if (!ShopGoodsSpecRelateRepository::decrement(['id' => $value['spec_relate_id']],'stock',$option.$value['number'])){
                $this->setError('扣除库存失败！');
                DB::rollBack();
                return false;
            }
        }
        $this->setMessage('扣除成功！');
        DB::commit();
        return true;
    }

    /**
     * 获取指定商品的规格json字符串
     * @param $goods_id
     * @return false|string
     */
    public static function getGoodsSpecJson($goods_id){
        if (!$spec_related = ShopGoodsSpecRelateRepository::getList(['goods_id' => $goods_id,'deleted_at' => 0],['spec_ids','stock','price'])){
            return '';
        }
        $spec_ids = array_column($spec_related,'spec_ids');
        $spec_str = '';
        foreach ($spec_ids as $id){
            $spec_str .= trim($id,',') . ',';
        }
        $spec_str   = trim($spec_str,',');
        $spec_ids   = array_unique(explode(',',$spec_str));
        $spec_list  = ShopGoodsSpecRepository::getAssignList($spec_ids,['id','image_id','spec_name','spec_value']);
        $spec_list  = ImagesService::getListImages($spec_list,['image_id' => 'single']);
        foreach ($spec_related as &$value){
            $value['spec'] = [];
            $value_spec_ids = explode(',',$value['spec_ids']);
            foreach ($value_spec_ids as $value_spec_id){
                if ($value_spec = self::searchArrays($spec_list,'id',$value_spec_id)){
                    $value['spec'][] = [
                        'spec_name'     => reset($value_spec)['spec_name'],
                        'spec_value'    => reset($value_spec)['spec_value'],
                        'image_id'      => reset($value_spec)['image_id'] ?? '',
                        'image_url'     => reset($value_spec)['image_url'] ?? '',
                    ];
                }
            }
            unset($value['spec_ids']);
        }
        return json_encode($spec_related);
    }

    /**
     * H5获取商品规格
     * @param $goods_id
     * @return array|null
     */
    public function getGoodsSpec($goods_id)
    {
        if (!$spec_related = ShopGoodsSpecRelateRepository::getList(['goods_id' => $goods_id,'deleted_at' => 0],['id','spec_ids','stock','price'])){
            $this->setMessage('暂无规格！');
            return [];
        }
        $spec_ids = array_column($spec_related,'spec_ids');
        $spec_str = '';
        foreach ($spec_ids as $id){
            $spec_str .= trim($id,',') . ',';
        }
        $spec_str   = trim($spec_str,',');
        $spec_ids   = array_unique(explode(',',$spec_str));
        $spec_list  = ShopGoodsSpecRepository::getAssignList($spec_ids,['id','image_id','spec_name','spec_value']);
        $spec_list  = ImagesService::getListImages($spec_list,['image_id' => 'single']);
        $res['spec_arr']   = [];
        foreach ($spec_list as $item){
            if ($spec = $this->searchArray($spec_list,'spec_name',$item['spec_name'])){
                foreach ($spec as &$value){
                    unset($value['image_id'],$value['spec_name']);
                }
                $res['spec_arr'][$item['spec_name']] = $spec;
            }
        }
        $res['spec_relate'] = $spec_related;
        foreach ($res['spec_relate'] as &$value){
            $value['price'] = sprintf('%.2f',round($value['price'] / 100,2));
            $value['img_url'] = '';
            $value_spec_ids = explode(',',$value['spec_ids']);
            foreach ($value_spec_ids as $value_spec_id){
                if ($value_spec = $this->searchArray($spec_list,'id',$value_spec_id)){
                    foreach ($value_spec as $v){
                        if (isset($v['image_url']) && !empty($v['image_url'])){
                            $value['img_url'] = $v['image_url'];break;
                        }
                    }
                }
            }
            $value['spec_ids'] = trim($value['spec_ids'],',');
        }
        $this->setMessage('获取成功！');
        return $res;
    }
}
            