<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class OaProcessRecordModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'oa_process_record';

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


    protected $fillable = ['id','business_id','process_id','process_category','position','node_id','path','action_result_id','operator_id','note','created_at','updated_at'];



}

 