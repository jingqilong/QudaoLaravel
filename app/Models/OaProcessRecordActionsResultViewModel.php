<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class OaProcessRecordActionsResultViewModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'oa_process_record_actions_result_view';

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
    protected $primaryKey = 'node_actions_result_id';


    protected $fillable = ['node_actions_result_id','node_action_id','node_id','action_id','action_result_id','actions_result_name','record_id','business_id','process_id','process_category','node_action_result_id','operator_id'];



}

 