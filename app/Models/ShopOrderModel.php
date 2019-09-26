<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ShopOrderModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'shop_order';
    
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
    protected $primaryKey = 'order_id';
    
    
    protected $fillable = ['order_id','order_sn','order_status','member_id','member_name','member_mobile','member_remark','goods_common_id','goods_id','goods_name','goods_num','goods_price','deliver_address','deliver_price','addtime','edittime','total_price','pay_sn','pay_time','pay_style','member_payment','pocket_type','pocket_value','introduce_member','introduce_remark'];
    
    

}
        
 