<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ScoreRecordViewModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'score_record_view';

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


    protected $fillable = ['id','member_id','score_type','score_name','expense_rate','cashing_rate','is_cashing','status','remnant_score','before_action_score','action','action_score','explain','created_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime'
    ];

}

 