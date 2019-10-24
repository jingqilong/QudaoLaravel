<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ScoreModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'score';

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


    protected $fillable = ['id','member_id','score_type','cause_type','incomes','outgoings','remain','created_at','updated_at'];



}

 