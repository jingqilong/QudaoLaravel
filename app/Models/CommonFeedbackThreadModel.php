<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class CommonFeedbackThreadModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'common_feedback_thread';

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


    protected $fillable = ['id','feedback_id','replay_id','content','status','operator_type','created_at','created_by'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'created_by' => 'datetime',
    ];


}

 