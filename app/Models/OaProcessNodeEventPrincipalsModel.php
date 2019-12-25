<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class OaProcessNodeEventPrincipalsModel extends Model
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'oa_process_node_event_principals';

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


    protected $fillable = ['id','node_id','node_action_id','principal_id','principal_iden'];
}