<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class TpAdminLoginLogModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'tp_admin_login_log';

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


    protected $fillable = ['id','a_admin','a_name','a_sex','a_phone','a_email','a_note','a_time','a_duty','a_permissions'];



}

 