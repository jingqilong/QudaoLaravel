<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ShopGoodsSpecRelateModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'shop_goods_spec_relate';

     /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 数据表中的主键
     *
     * @var bool
     */
    protected $primaryKey = 'id';


    protected $fillable = ['id','goods_id','spec_ids','stock','price','created_at','updated_at','deleted_at'];

}

 