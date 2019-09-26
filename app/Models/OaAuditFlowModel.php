<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class OaAuditFlowModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'oa_audit_flow';

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


    protected $fillable = ['id','type_id','step','audit_deleget_type','audit_role_id','audit_employee_id','agent_deleget_type','agent_role_id','agent_employee_id','accept_next','reject_next','is_finish'];



}

 