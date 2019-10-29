<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class HouseDetailsViewModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'house_details_view';

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


    protected $fillable = ['id','title','area_code','address','describe','rent','tenancy','leasing_id','leasing_title','decoration','height','area','image_ids','storey','unit_id','unit_title','condo_name','toward_id','toward_title','category','publisher','publisher_id','facilities_ids','status','recommend','created_at','updated_at','deleted_at'];



}

 