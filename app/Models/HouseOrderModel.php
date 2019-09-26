<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class HouseOrderModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'house_order';
    
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
    protected $primaryKey = 'or_id';
    
    
    protected $fillable = ['or_id','or_eid','or_user','or_uphone','or_openid','or_utime','or_content','or_time','or_manager','or_mphone'];
    
    

}
        
 