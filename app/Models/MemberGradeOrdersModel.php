<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MemberGradeOrdersModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'member_grade_orders';

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


    protected $fillable = ['id','member_id','previous_grade','grade','amount','validity','order_no','status','audit','created_at','updated_at'];



}

 