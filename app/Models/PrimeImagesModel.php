<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class PrimeImagesModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'prime_images';
    
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
    protected $primaryKey = 'i_id';
    
    
    protected $fillable = ['i_id','i_image','i_pid','i_rid','i_nid','i_fid','i_fname'];
    
    

}
        
 