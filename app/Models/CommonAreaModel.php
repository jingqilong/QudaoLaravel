<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class CommonAreaModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'common_area';

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


    protected $fillable = ['id','code','parent_code','name','short_name','level','lng','lat','sort','memo','image_url','state','created_at','updated_at'];



}

 