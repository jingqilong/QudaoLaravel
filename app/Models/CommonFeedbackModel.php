<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class CommonFeedbackModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'common_feedback';
    
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
    protected $primaryKey = 'f_id';
    
    
    protected $fillable = ['f_id','f_phone','f_content','f_time'];
    
    

}
        
 