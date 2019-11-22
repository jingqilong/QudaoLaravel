<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MedicalOrdersViewModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'medical_orders_view';

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


    protected $fillable = ['id','member_id','member_name','name','mobile','sex','age','type','end_time','hospital_id','hospital_name','doctor_id','doctor_head_url','doctor_title','appointment_at','status','created_at','updated_at','deleted_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'appointment_at' => 'datetime',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
        'end_time'       => 'datetime',
    ];


}

 