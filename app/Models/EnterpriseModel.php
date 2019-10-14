<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class EnterpriseModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'enterprise_order';
    
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
    
    
    protected $fillable = ['id','name','mobile','enterprise_name','service_type','status','remark','created_at','reservation_at','updated_at'];

    

}
        
 