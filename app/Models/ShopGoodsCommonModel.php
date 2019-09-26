<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ShopGoodsCommonModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'shop_goods_common';
    
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
    protected $primaryKey = 'goods_common_id';
    
    
    protected $fillable = ['goods_common_id','goods_name','goods_code','goods_unit','spec_name','spec_value','main_img','desc_img','adve_img','goods_remark','goods_status','goods_store','goods_category','goods_addtime','goods_edittime','is_hidden','goods_pocket_type'];
    
    

}
        
 