<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MemberTradesModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'member_trades';

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


    protected $fillable = ['id','trade_no','transaction_no','order_id','pay_user_id','payee_user_id','amount','fund_flow','trade_method','status','create_at','end_at'];



}

 