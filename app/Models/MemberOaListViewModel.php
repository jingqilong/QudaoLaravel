<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MemberOaListViewModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'member_oa_list_view';

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


    protected $fillable = ['id','card_no','mobile','email','ch_name','sex','status','hidden','grade','title','position','category','address','is_recommend','is_home_detail','img_url','created_at','end_at','updated_at','deleted_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'end_at'     => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];


}

 