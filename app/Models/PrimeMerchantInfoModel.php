<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class PrimeMerchantInfoModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'prime_merchant_info';

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


    protected $fillable = ['id','merchant_id','type','license','license_img_id','area_code','banner_ids','display_img_ids','address','shorttitle','describe','star','expect_spend','discount','created_at','updated_at'];



}

 