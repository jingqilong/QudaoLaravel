<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MessageSendViewModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'message_send_view';

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


    protected $fillable = ['id','user_id','user_type','message_id','message_category','category_id','title','content','relate_id','image_ids','url','created_at','updated_at','deleted_at'];



}

 