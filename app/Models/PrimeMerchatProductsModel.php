<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class PrimeMerchatProductsModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'prime_merchat_products';

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


    protected $fillable = ['id','merchant_id','type','title','describe','price','image_ids','is_recommend','created_at','updated_at'];



}

 