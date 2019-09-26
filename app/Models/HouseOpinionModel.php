<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class HouseOpinionModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'house_opinion';
    
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
    protected $primaryKey = 'o_id';
    
    
    protected $fillable = ['o_id','o_user','o_uphone','o_time','o_content','o_openid'];
    
    

}
        
 