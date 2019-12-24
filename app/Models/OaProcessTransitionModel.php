<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class OaProcessTransitionModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'oa_process_transition';

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


    protected $fillable = ['id','process_id','node_action_result_id','current_node','next_node','status','created_at','updated_at'];



}

 