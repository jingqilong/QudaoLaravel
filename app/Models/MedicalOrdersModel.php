<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MedicalOrdersModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'medical_orders';

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


    protected $fillable = ['id','member_id','name','sex','age','hospitals_id','doctor_id','appointment_at','created_at','updated_at','deleted_id'];



}

 