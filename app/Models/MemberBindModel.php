<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MemberBindModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'member_bind';

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


    protected $fillable = ['id','user_id','identity_type','identifier','credential','verified_at','last_login','ip_address','additional','created_at','created_by'];



}

 