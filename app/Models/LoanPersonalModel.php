<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class LoanPersonalModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'loan_personal';
    
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
    
    
    protected $fillable = ['id','name','mobile','price','ent_name','ent_title','address','appointment','status','remark','created_at','updated_at','reservation_at','deleted_at'];

    

}
        
 