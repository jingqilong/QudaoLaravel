<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MemberGradeOrdersViewModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'member_grade_orders_view';

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


    protected $fillable = ['id','member_id','mobile','ch_name','sex','previous_grade','previous_grade_title','grade','grade_title','amount','validity','order_no','status','audit','created_at','updated_at'];



}

 