<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class EventSiteimgModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'event_siteimg';
    
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
    protected $primaryKey = 'si_id';
    
    
    protected $fillable = ['si_id','si_image','si_aid','si_cate'];
    
    

}
        
 