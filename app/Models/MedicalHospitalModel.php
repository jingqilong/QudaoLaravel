<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MedicalHospitalModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'medical_hospital';
    
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
    protected $primaryKey = 'h_id';
    
    
    protected $fillable = ['h_id','h_hospital','h_introduction','h_facilities','h_address','h_photo','h_adept'];
    
    

}
        
 