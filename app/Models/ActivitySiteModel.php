<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ActivitySiteModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'activity_site';

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


    protected $fillable = ['id','title','address','name','theme_id','image_ids','labels','scale','created_at','updated_at'];



}

 