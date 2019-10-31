<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MedicalDoctorsModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'medical_doctors';

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


    protected $fillable = ['id','member_id','name','sex','title','good_at','introduction','recommend','doctor_id','department_id','created_at','updated_at'];



}

 