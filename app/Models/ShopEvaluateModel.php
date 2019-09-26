<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ShopEvaluateModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'shop_evaluate';
    
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
    
    
    protected $fillable = ['id','e_star','e_goods_id','e_keywords','e_text','e_name','e_headimg','e_mid','e_onumber','e_time','e_status','e_main_img'];
    
    

}
        
 