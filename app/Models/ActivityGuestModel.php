<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ActivityGuestModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'activity_guest';

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


    protected $fillable = ['id','activity_id','avatar','name','company','job','member_id','created_at','updated_at'];



}

 