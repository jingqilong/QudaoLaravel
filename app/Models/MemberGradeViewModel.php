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


    protected $fillable = ['id','card_no','en_name','birthday','info_provider','image_id','email','end_at','other_server','profile','sex','ch_name','mobile','position','address','employer','grade','is_recommend','img_url','title','category','status','hidden','created_at','deleted_at'];



}

 