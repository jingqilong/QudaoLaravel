<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class HousePhotoModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'house_photo';
    
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
    
    
    protected $fillable = ['p_id','p_photo','p_eid'];
    
    

}
        
 