<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class OaProcessNodeActionsResultViewModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'oa_process_node_actions_result_view';

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


    protected $fillable = ['id','node_action_id','node_id','node_name','process_id','process_name','category_id','category_name','action_result_id','action_result_name'];



}

 