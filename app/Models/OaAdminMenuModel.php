<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class OaAdminMenuModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'oa_admin_menu';

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


    protected $fillable = ['id','type','parent_id','path','level','order','title','icon','url','permission','created_at','updated_at'];



}

 