<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ShopOrderRelateModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'shop_order_relate';

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


    protected $fillable = ['id','order_id','status','express_company','express_price','express_number','address_id','shipment_at','created_at','updated_at','deleted_at'];



}

 