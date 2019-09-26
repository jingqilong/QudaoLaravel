<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class HouseAppointModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'house_appoint';
    
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
    protected $primaryKey = 'a_id';
    
    
    protected $fillable = ['a_id','a_contact','a_cphone','a_time','a_content','a_eid','a_user','a_uphone'];
    
    

}
        
 