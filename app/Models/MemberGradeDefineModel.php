<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MemberGradeDefineModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'member_grade_define';

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


    protected $fillable = ['id','iden','title','description','status','minimum_time','amount','is_buy','created_at','updated_at'];



}

 