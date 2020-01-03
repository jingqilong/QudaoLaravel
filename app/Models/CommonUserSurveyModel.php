<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class CommonUserSurveyModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'common_user_survey';

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


    protected $fillable = ['id','name','gender','mobile','request','hear_from','status','created_at','updated_by','updated_at'];



}

 