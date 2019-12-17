<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ActivityRegisterViewModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'activity_register_view';

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


    protected $fillable = [
        'id',
        'activity_id',
        'name',
        'area_code',
        'address',
        'price',
        'theme_name',
        'theme_icon',
        'start_time',
        'end_time',
        'cover_url',
        'member_id',
        'order_no',
        'register_status',
        'is_register',
        'sign_in_code',
        'created_at',
        'updated_at'
    ];



}

 