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


    protected $fillable = ['id','name','address','price','theme_id','start_time','end_time','site_id','supplies_ids','is_recommend','banner_ids','image_ids','firm_ids','notice','detail','created_at','updeted_at'];



}

 