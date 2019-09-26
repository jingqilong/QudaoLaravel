<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class EventActivityModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'event_activity';
    
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
    protected $primaryKey = 'a_id';
    
    
    protected $fillable = ['a_id','a_title','a_address','a_price','a_category','a_start','a_end','a_site','a_supplies','a_tag','a_link','a_ycon','a_parameter','a_cate','a_img','a_type','a_scale','a_ziying','a_firm','a_notice','a_intor','a_schedule'];
    
    

}
        
 