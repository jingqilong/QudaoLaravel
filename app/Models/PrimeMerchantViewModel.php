<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class PrimeMerchantViewModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'prime_merchant_view';
    
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
    
    
    protected $fillable = ['id','name','account','mobile','realname','logo_id','type','license','license_img_id','area_code','longitude','latitude','banner_ids','display_img_ids','address','shorttitle','describe','expect_spend','discount','disabled','is_recommend','created_at','updated_at'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

}
        
 