<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class OaAdminRolePermissionsModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'oa_admin_role_permissions';

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
    protected $primaryKey = 'role_id';


    protected $fillable = ['role_id','permission_id','created_at','updated_at'];



}

 