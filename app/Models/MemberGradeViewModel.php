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
    protected $table = 'member_show_list_view';

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


    protected $fillable = ['id','card_no','mobile','email','ch_name','en_name','sex','avatar_id','is_test','status','hidden','referral_code','created_at','updated_at','deleted_at','grade','employer','position','title','industry','category','profile','birthday','address','is_home_detail','degree','is_recommend','img_url'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];


}

 