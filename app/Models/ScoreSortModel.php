<?php     
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ScoreSortModel extends Model
{
    
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'score_sort';
    
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
    
    
    protected $fillable = ['id','a_aid','a_jichang_sort','a_jiu_sort','a_doctor_sort','a_consume_sort','a_jinbi_sort','a_winning_sort'];
    
    

}
        
 