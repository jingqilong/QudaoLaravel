<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class OaProcessDefinitionModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'oa_process_definition';

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


    protected $fillable = ['id','name','catagory_id','description','status','created_at','updated_at'];



}

 