<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ShopGoodsSpecModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'shop_goods_spec';

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


    protected $fillable = ['id','goods_id','image_id','spec_name','spec_value','created_at','updated_at','deleted_at'];



}

 