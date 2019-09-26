<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MedicalDepartmentModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'medical_department';
    
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
    protected $primaryKey = 'de_Id';
    
    
    protected $fillable = ['de_Id','de_department','de_hid','de_doid'];
    
    

}
        
 