<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MedicalLabelModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'medical_label';
    
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
    protected $primaryKey = 'la_id';
    
    
    protected $fillable = ['la_id','la_label','la_doid'];
    
    

}
        
 