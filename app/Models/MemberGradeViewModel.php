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
    protected $table = 'member_grade_info_view';

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


    protected $fillable = ['id','card_no','en_name','sex','ch_name','mobile','position','address','employer','grade','img_url','title','category','status','hidden','created_at','deleted_at'];



}

 