<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class PrimeProjectModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'prime_project';
    
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
    protected $primaryKey = 'p_id';
    
    
    protected $fillable = ['p_id','p_little','p_title','p_category','p_introduce','p_price','p_address','p_=====','p_features','p_activity','p_serve','p_supplier','p_merit','p_refund','p_gift','p_introimg','p_bestfood','p_route','p_schedule','p_country','p_star','p_cprice','p_eid','p_image','p_exclusive','p_shopid'];
    
    

}
        
 