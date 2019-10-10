<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class OaProcessTransitionDetailViewModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'oa_process_transtion_detail_view';

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
    protected $primaryKey = 'transition_id';


    protected $fillable = ['transition_id','node_action_id','node_id','process_id','current_node','action_related_id','next_node','created_at','updated_at'];



}

 