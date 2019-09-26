<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class PrimeAuditModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'prime_audit';
    
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
    
    
    protected $fillable = ['id','order_id','gc_id','pay','mid','audit','add_time','source'];
    
    

}
        
 