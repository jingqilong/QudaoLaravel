<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ActivityPastViewModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'activity_past_view';

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



    protected $fillable = ['id','activity_id','name','top','type','file_type','img_url','is_recommend','address','start_time','end_time','resource_ids','presentation','hidden','created_at','updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];
}

 