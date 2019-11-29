<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ShopOrderRelateViewModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'shop_order_relate_view';

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


    protected $fillable = [
        'id',
        'order_id',
        'member_id',
        'status',
        'express_company_id',
        'express_price',
        'express_number',
        'address_id',
        'remarks',
        'receive_method',
        'order_no',
        'trade_id',
        'order_type',
        'amount',
        'payment_amount',
        'score_deduction',
        'score_type',
        'order_status',
        'receive_name',
        'receive_mobile',
        'receive_area_code',
        'receive_address',
        'income_score',
        'member_name',
        'member_mobile',
        'shipment_at',
        'receive_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

}

 