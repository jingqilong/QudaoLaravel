<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class EventOrderModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'event_order';
    
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
    
    
    protected $fillable = ['o_id','o_number','o_aid','o_cardid','o_name','o_phone','o_work','o_position','o_text','o_price','o_time','o_num','o_address','o_cate','o_state','o_aprice','o_openid','o_alink','o_mail','o_wechat'];
    
    

}
        
 