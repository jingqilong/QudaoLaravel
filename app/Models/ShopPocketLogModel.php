<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ShopPocketLogModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'shop_pocket_log';
    
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
    
    
    protected $fillable = ['id','mid','pocket_type','pocket_value','pocket_act','pocket_frm','frm_data','admin_id','addtime'];
    
    

}
        
 