<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MemberViewModel extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'member_view';

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


    protected $fillable = ['id' ,'card_no' ,'mobile' ,'email' ,'ch_name' ,'en_name' ,'sex' ,'avatar_id' ,'password' ,'status' ,'hidden' ,'referral_code' ,'grade' ,'employer' ,'position' ,'title' ,'industry' ,'brands' ,'run_wide' ,'profile' ,'category' ,'goodat' ,'id_card' ,'address' ,'fixedphone' ,'paperexpo' ,'zipcode' ,'degree' ,'school' ,'constellation' ,'zodiac' ,'birthplace' ,'remarks' ,'referral_agency' ,'info_provider' ,'archive' ,'created_at' ,'updated_at' ,'deleted_at'];



}

 