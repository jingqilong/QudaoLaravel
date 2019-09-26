<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MedicalOrderModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'medical_order';
    
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
    protected $primaryKey = 'order_Id';
    
    
    protected $fillable = ['order_Id','order_number','order_user','order_usex','order_uage','order_uphone','order_ustart','order_uend','order_describe','order_doctor','order_dophone','order_dohospital','order_dodepar','order_choose','order_time','order_price','order_money','order_state','order_evid','order_evstate'];
    
    

}
        
 