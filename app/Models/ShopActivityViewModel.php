<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ShopActivityViewModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'shop_activity_view';

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


    protected $fillable = ['id','goods_id','type','status','show_image','stop_time','name','category','price','details','banner_ids','image_ids','stock','express_price','score_deduction','score_categories','gift_score','is_recommend','goods_status','created_at','updated_at','deleted_at','labels'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

}

 