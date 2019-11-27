<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MemberPersonalServiceModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'member_personal_service';

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


    protected $fillable = ['id','member_id','publicity','protocol','nameplate','attendant','member_attendant','gift','created_at','update_at'];



}

 