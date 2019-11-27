<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MemberInfoModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'member_info';

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


    protected $fillable = ['id','member_id','grade','employer','position','title','industry','brands','run_wide','category','profile','goodat','birthday','id_card','address','fixedphone','paperexpo','zipcode','degree','school','constellation','zodiac','birthplace','remarks','referral_agency','info_provider','archive','created_at','update_at'];



}

 