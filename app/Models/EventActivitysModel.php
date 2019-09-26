<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class EventActivitysModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'event_activitys';
    
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
    
    
    protected $fillable = ['id','name','start_time','end_time','place','host','head','price','integral','sponsors','linkman','mobile','operation','activity_category','state','remark','main_img','main2_img','main3_img','main4_img','activity_detailes','time','itinerary'];
    
    

}
        
 