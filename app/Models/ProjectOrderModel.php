<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ProjectOrderModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'project_order';
    
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
    
    
    protected $fillable = ['id','name','mobile','project_name','status','remark','created_at','reservation_at','updated_at'];

    

}
        
 