<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ActivityDetailModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'activity_detail';

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


    protected $fillable = ['id','name','area_code','address','price','longitude','latitude','theme_id','signin','start_time','end_time','site_id','supplies_ids','is_recommend','links','cover_id','banner_ids','image_ids','status','firm','notice','detail','is_member','need_audit','stop_selling','max_number','created_at','updated_at','deleted_at'];



}

 