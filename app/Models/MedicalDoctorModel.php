<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MedicalDoctorModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'medical_doctor';
    
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
    protected $primaryKey = 'do_Id';
    
    
    protected $fillable = ['do_Id','do_doctor','do_photo','do_phone','do_deid','do_hid','do_adept','do_intorduction','do_position','do_label'];
    
    

}
        
 