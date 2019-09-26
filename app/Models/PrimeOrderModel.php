<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class PrimeOrderModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'prime_order';
    
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
    protected $primaryKey = 'o_id';
    
    
    protected $fillable = ['o_id','o_number','o_user','o_phone','o_openid','o_time','o_shopid','o_content','o_pid','o_rid','o_pcate','o_atime','o_start','o_end','o_nop','o_around','o_invoice','o_invoiceh','o_ein','o_fphone','o_invemail','o_hopinion','o_insurance','o_member','o_child','o_state','o_aprace','o_wechatnum','o_wechatmc','o_wechattime','o_foodstart','o_integral','o_allrmb','o_source'];
    
    

}
        
 