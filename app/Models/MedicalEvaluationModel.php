<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MedicalEvaluationModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'medical_evaluation';
    
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
    protected $primaryKey = 'ev_id';
    
    
    protected $fillable = ['ev_id','ev_number','ev_doctor','ev_mylevel','ev_all','ev_dlevel','ev_user','ev_state','ev_content','ev_time'];
    
    

}
        
 