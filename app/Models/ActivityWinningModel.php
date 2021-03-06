<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ActivityWinningModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'activity_winning';

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


    protected $fillable = ['id','member_id','activity_id','prize_id','is_get','created_at'];



}

 