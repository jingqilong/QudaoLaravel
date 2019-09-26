<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class OaPushSubscriptionsModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'oa_push_subscriptions';

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


    protected $fillable = ['id','subscribable_type','subscribable_id','endpoint','public_key','auth_token','content_encoding','created_at','updated_at'];



}

 