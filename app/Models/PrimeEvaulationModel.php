<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class PrimeEvaulationModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'prime_evaulation';
    
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
    protected $primaryKey = 'e_id';
    
    
    protected $fillable = ['e_id','e_level','e_label','e_content','e_name','e_cardid','e_userimg','e_pid','e_oid','e_time'];
    
    

}
        
 