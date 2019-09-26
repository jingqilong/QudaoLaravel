<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class HouseEstateModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'house_estate';
    
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
    
    
    protected $fillable = ['e_id','e_title','e_regional','e_address','e_describe','e_rent','e_payment','e_conditions','e_height','e_area','e_floor','e_housetype','e_community','e_toward','e_category','e_time','e_collection','e_uphone','e_openid','e_release','e_bed','e_tv','e_kongtiao','e_washer','e_heating','e_balcony','e_wardrobe','e_calorifier','e_microwave','e_refrigerator','e_kitchen','e_gas','e_furniture','e_toilet','e_parking','e_state'];
    
    

}
        
 