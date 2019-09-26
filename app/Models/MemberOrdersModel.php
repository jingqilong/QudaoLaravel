<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MemberOrdersModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'member_orders';

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


    protected $fillable = ['id','order_no','trade_id','user_id','order_type','amount','payment_amount','status','create_at','updated_at'];



}

 