<?php
namespace App\Services\Shop;


use App\Enums\ShopGoodsEnum;
use App\Repositories\CommonImagesRepository;
use App\Repositories\ScoreCategoryRepository;
use App\Repositories\ShopGoodSpecListViewRepository;
use App\Repositories\ShopGoodsRepository;
use App\Repositories\ShopGoodsSpecRelateRepository;
use App\Repositories\ShopGoodsSpecRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
use App\Traits\HelpTrait;
use Illuminate\Support\Arr;
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
    protected function getListCommonInfo($goods_spec_arr, $goods_column=['id','name','price','banner_ids','score_deduction','score_categories','negotiable']){
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
        $goods_list         = ImagesService::getListImages($goods_list,['banner_ids' => 'single']);
        $spec_ids           = implode(',',array_column($spec_relate_list,'spec_ids'));
        $spec_list          = ShopGoodsSpecRepository::getAssignList(explode(',',$spec_ids),['id','spec_value','spec_name']);
        $result             = [];
        foreach ($goods_spec_arr as $key => $value){
            $spec_str       = '件';
            $price          = 0;
            if (isset($value['spec_relate_id'])){
                $spec_str = '';
                if ($spec_relate = $this->searchArray($spec_relate_list,'id',$value['spec_relate_id'])){
                    $price = reset($spec_relate)['price'];
                    $value_spec_ids = explode(',',trim(reset($spec_relate)['spec_ids'],','));
                    foreach ($value_spec_ids as $value_spec_id){
                        if ($item_spec  = $this->searchArray($spec_list,'id',$value_spec_id)){
                            $spec_str  .= reset($item_spec)['spec_name'] .':'. reset($item_spec)['spec_value'] . ';';
                        }
                    }
                }
            }

            if ($goods  = $this->searchArray($goods_list,'id',$value['goods_id'])){
                $goods  =  reset($goods);
                $price  =  ($price ? $price : $goods['price']) * $value['number'];
                $deduction_price = $goods['negotiable'] == ShopGoodsEnum::NEGOTIABLE ? 0 : $this->maximumCreditDeductionAmount(
                    $goods['score_categories'],
                    $goods['score_deduction'],
                    $value['number'],
                    $price
                );
                $result[$key] = [
                    'goods_name'      => $goods['name'],
                    'goods_price'     => $goods['negotiable'] == ShopGoodsEnum::NEGOTIABLE ? '面议' : sprintf('%.2f',round($price / 100,2)),
                    'main_img_url'    => $goods['banner_url'],
                    'number'          => $value['number'],
                    'deduction_price' => $deduction_price//最高积分抵扣
                ];
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
        $spec_relate_list   = createArrayIndex($spec_relate_list,'id');
        $goods_ids          = array_column($goods_spec_arr,'goods_id');
        $goods_list         = ShopGoodsRepository::getAssignList($goods_ids);
        $goods_list         = createArrayIndex($goods_list,'id');
        foreach ($goods_spec_arr as $key => $value){
            $goods = $goods_list[$value['goods_id']];
            if (!isset($value['spec_relate_id'])){
                if ($value['number'] > $goods['stock']){
                    $this->setError('商品【'.$goods['name'].'】库存不足！');
                    return false;
                }
                break;
            }
            if (isset($spec_relate_list[$value['spec_relate_id']])){
                $spec_relate = $spec_relate_list[$value['spec_relate_id']];
                if ($value['number'] > $spec_relate['stock']){
                    $this->setError('商品【'.$goods['name'].'】库存不足！');
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
            if (!isset($value['spec_relate_id']) || empty($value['spec_relate_id'])){
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
            $value['price']= round($value['price'] / 100,2);
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
        if (!$spec_related = ShopGoodsSpecRelateRepository::getList(['goods_id' => $goods_id,'deleted_at' => 0,'stock' => ['>',0]],['id','spec_ids','stock','price'])){
            $this->setMessage('暂无规格！');
            return [];
        }
        #获取数据列表中所有的规格id，用于一次性查询
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
                    //规格对应显示，用于前端方便展示
                    $value['show'] = [];
                    if ($like_spec = $this->likeSearchArray($spec_related,'spec_ids',','.$value['id'].',')){
                        foreach ($like_spec as $v){
                            if (!empty($v['stock'])){
                                $value['show'] = array_merge($value['show'],explode(',',trim($v['spec_ids'],',')));
                            }
                        }
                        $value['show'] = array_values(array_diff($value['show'],[$value['id']]));
                    }
                    unset($value['image_id'],$value['spec_name']);
                }
                $res['spec_arr'][$item['spec_name']] = $spec;
            }
        }
        $spec_arr = [];
        foreach ($spec_related as &$value){
            $key = trim($value['spec_ids'],',');
            $spec_arr[$key]['id']       = $value['id'];
            $spec_arr[$key]['stock']       = $value['stock'];
            $spec_arr[$key]['price'] = sprintf('%.2f',round($value['price'] / 100,2));
            $spec_arr[$key]['img_url'] = '';
            $value_spec_ids = explode(',',$value['spec_ids']);
            foreach ($value_spec_ids as $value_spec_id){
                if ($value_spec = $this->searchArray($spec_list,'id',$value_spec_id)){
                    foreach ($value_spec as $v){
                        if (isset($v['image_url']) && !empty($v['image_url'])){
                            $spec_arr[$key]['img_url'] = $v['image_url'];break;
                        }
                    }
                }
            }
        }
        $res['spec_relate'] = $spec_arr;
        $this->setMessage('获取成功！');
        return $res;
    }

    /**
     * H5获取商品规格
     * @param $goods_id
     * @return array|null
     */
    public function getGoodsSpecList($goods_id)
    {
        //从视图中取出记录
        if (!$spec_list = ShopGoodSpecListViewRepository::getAllList(
                ['goods_id' => $goods_id,'deleted_at' => 0,'stock' => ['>',0]],
                ['id','goods_id','price','stock','spec_ids','attribute_id','image_id','spec_name','spec_value'],
                ['id','attribute_id'],
                ['asc','asc']
        )){
            $this->setMessage('暂无规格！');
            return [];
        }
        $result = $last_item = $new_item =  $attributes = [];
        $last_id = 0;
        //因为视图是联表查询，所以，这里要合并记录
        foreach ($spec_list as $item){
            if($last_id != $item['id']){
                $new_item['attributes'] = implode('； ',$attributes);
                $last_id = $item['id'];
                $new_item = & $result[];
                $new_item = Arr::only($item,['id','goods_id','price','stock','image_id']);
                $new_item['price']  = number_format($new_item['price']/100,2 );
                $attributes = array_flip(explode(',',trim($item['spec_ids'],','))) ;
            }
            $attributes[$item['attribute_id']]  =  $item['spec_name'] ."：" .$item['spec_value'] ;
            if($new_item['image_id'] == 0){
                if( $item['image_id']!=0){
                    $new_item['image_id'] = $item['image_id'];
                }
            }
        }
        $new_item['attributes'] = implode('； ',$attributes);
        //获取图片
        $result  = ImagesService::getListImages($result,['image_id' => 'single']);
        $this->setMessage('获取成功！');
        return $result;
    }


    /**
     * 计算最大积分抵扣金额
     * @param $str_type
     * @param $score
     * @param int $number
     * @param null $goods_price
     * @return float|int
     */
    public function maximumCreditDeductionAmount($str_type, $score, $number = 1,$goods_price = null)
    {
        if (empty($str_type) || empty($score)){
            return 0;
        }
        if (!$type_list = ScoreCategoryRepository::getAll()){
            return 0;
        }
        $types = explode(',',$str_type);
        $max_price = 0;
        foreach ($types as $type){
            if ($search_type = $this->searchArray($type_list,'id',$type)){
                $expense_rate = reset($search_type)['expense_rate'];
                $price        = $number * $expense_rate * $score;
                if ($price > $max_price){
                    $max_price = $price;
                }
                if (is_null($goods_price)){
                    continue;
                }
                $total_price = ($goods_price / 100) * $number;
                if ($total_price < $max_price){
                    $max_price = $total_price;
                    break;
                }
            }
        }

        return $max_price;
    }

    /**
     * 获取面议商品信息
     * @param array $goods_param
     * @return array
     */
    protected function getNegotiableGoodsInfo($goods_param){
        $goods_ids          = array_column($goods_param,'goods_id');
        $goods_column       = ['id','name','price','banner_ids','score_deduction','score_categories'];
        $goods_list         = ShopGoodsRepository::getList(['id' => ['in',$goods_ids],'negotiable' => ShopGoodsEnum::NEGOTIABLE],$goods_column);
        array_walk($goods_list, function(&$value) {
            $banner_ids = trim($value['banner_ids'],',' );
            $banner_ids = explode(',',$banner_ids);
            $value['banner_ids'] = reset($banner_ids);
        });
        $goods_list         = CommonImagesRepository::bulkHasOneWalk($goods_list, ['from' => 'banner_ids','to' => 'id'], ['img_url','id'],[],
            function ($src_item,$set_items){
                $src_item['banner_url'] = $set_items['img_url'];
                return $src_item;
            }
        );
        $goods_list         = createArrayIndex($goods_list,'id');
        $spec_relate_ids    = array_column($goods_param,'spec_relate_id');
        $spec_relate_list   = ShopGoodsSpecRelateRepository::getStrSpecList($spec_relate_ids);
        $result             = [];
        foreach ($goods_param as $item){
            if (!isset($goods_list[$item['goods_id']]))continue;
            $goods = $goods_list[$item['goods_id']];
            $result[] = [
                'goods_id'        => $goods['id'],
                'goods_name'      => $goods['name'],
                'goods_price'     => '面议',
                'main_img_url'    => $goods['banner_url'],
                'number'          => $item['number'],
                'spec_relate_id'  => $item['spec_relate_id'] ?? 0,
                'spec'            => $spec_relate_list[($item['spec_relate_id'] ?? 0)] ?? '',
                'cart_id'         => $item['cart_id'] ?? 0
                ];
        }
        $this->setMessage('获取成功！');
        return $result;
    }
}
            