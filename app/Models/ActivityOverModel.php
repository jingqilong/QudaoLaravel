<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ActivityOverModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'activity_over_details';

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



    protected $fillable = ['id','activity_id','banner_video_id','img_ids','presentation_1','presentation_2','presentation_3','presentation_4','presentation_5','created_at'];


}

 