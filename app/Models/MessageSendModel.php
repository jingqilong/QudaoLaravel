<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MessageSendModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'message_send';

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


    protected $fillable = ['id','user_id','user_type','message_id','created_at','deleted_at'];



}

 