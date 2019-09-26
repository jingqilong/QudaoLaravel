<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class EventCollectModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'event_collect';
    
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
    protected $primaryKey = 'c_id';
    
    
    protected $fillable = ['c_id','c_aid','c_cardid','c_time'];
    
    

}
        
 