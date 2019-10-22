<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ActivityCommentsModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'activity_comments';

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


    protected $fillable = ['id','content','comment_name','comment_avatar','activity_id','member_id','status','hidden','created_at','deleted_at'];



}

 