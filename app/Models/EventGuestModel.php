<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class EventGuestModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'event_guest';
    
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
    protected $primaryKey = 'g_id';
    
    
    protected $fillable = ['g_id','g_aid','g_headimg','g_name','g_work','g_pos','g_mid'];
    
    

}
        
 