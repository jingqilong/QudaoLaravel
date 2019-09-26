<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ShopGoodsModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'shop_goods';
    
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
    protected $primaryKey = 'goods_id';
    
    
    protected $fillable = ['goods_id','goods_common_id','goods_name','goods_spec','goods_price','goods_storage','goods_salenum','goods_lock','goods_pocket','goods_status','addtime','edittime','goods_pocket_a','goods_pocket_d'];
    
    

}
        
 