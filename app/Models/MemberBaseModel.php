<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MemberBaseModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'member_base';

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


    protected $fillable = ['id','card_no','mobile','email','ch_name','en_name','sex','avatar_id','password','status','hidden','referral_code','created_at','update_at','deleted_at'];



}

 