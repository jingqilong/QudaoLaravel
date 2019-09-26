<?php     
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

class MemberModel extends Authenticatable implements JWTSubject
{

    use Notifiable;

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'member';
    
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
    protected $primaryKey = 'm_id';
    
    
    protected $fillable = ['m_id','m_num','m_cname','m_ename','m_sex','m_groupname','m_workunits','m_position','m_socialposition','m_industry','m_category','m_introduce','m_img','m_time','m_phone','m_birthday','m_numcard','m_email','m_address','m_openid','m_fixedphone','m_idcard','m_zipcode','m_works','m_socials','m_lifes','m_arts','m_intacts','m_intactbest','m_goodat','m_degree','m_school','m_constellation','m_zodiac','m_birthplace','m_notes','m_indate','m_referrerid','m_referrername','m_integrals','m_savings','m_storefront','m_nosavings','m_allsavings','m_pushid','m_pushname','m_oldid','m_connection','m_doctors','m_airport','m_finance','m_private','m_cameras','m_magazine','m_services','m_wechatshow','m_wechattext','m_actname','m_tcspwdl','m_brandstrat','m_pamanager','m_memberships','m_brands','m_business','m_recby','m_zipaddress','m_tablemer','m_bcardid','m_gifthand','m_infop','m_starte','m_wechatid','m_sort','m_openids','m_password','m_demand','m_stored','m_consume_sort','m_consume_num','m_alipay','m_opened_people','m_major','m_recognize','m_referral_code'];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'm_password',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        // TODO: Implement getJWTIdentifier() method.
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        // TODO: Implement getJWTCustomClaims() method.
        return [];
    }
}
        
 