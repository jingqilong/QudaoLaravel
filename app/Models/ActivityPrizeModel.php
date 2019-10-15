<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ActivityPrizeModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'activity_prize';

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


    protected $fillable = ['id','activity_id','name','number','odds','image_ids','worth','link','created_at','updated_at'];



}

 