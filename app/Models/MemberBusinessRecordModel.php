<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MemberBusinessRecordModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'member_business_record';

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


    protected $fillable = ['id','relation_id','service_type','status','desc','created_at','deleted_at'];



}

 