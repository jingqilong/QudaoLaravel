<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MemberGradeViewModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'member_grade_view';

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
    protected $primaryKey = 'user_id';


    protected $fillable = ['user_id','grade','view_grade','created_at','updated_at'];



}

 