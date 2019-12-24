<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class OaProcessActionRelatedModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'oa_process_action_related';

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


    protected $fillable = ['id','node_action_id','action_result_id','action_result','event_ids','transition_id','created_at','updated_at'];



}

 