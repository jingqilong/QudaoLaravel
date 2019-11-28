<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class HouseReservationModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'house_reservation';

     /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * 数据表中的主键
     *
     * @var bool
     */
    protected $primaryKey = 'id';
    /**
   * 模型日期列的存储格式
   *
   * @var string
   */
    protected $dateFormat = 'U';

    protected $fillable = ['id','house_id','name','mobile','time','memo','member_id','state','created_at','updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

}

 