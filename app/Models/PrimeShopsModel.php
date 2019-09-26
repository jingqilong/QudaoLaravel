<?php     
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PrimeShopsModel extends Authenticatable implements JWTSubject
{

    use Notifiable;

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'prime_shops';
    
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
    
    
    protected $fillable = ['id','user','pwd','account','name','shopid','time','telephone','phone','active','remark','category','linkman','address','business_img','restaurantNote','foodNote','memberExclusive','banner_img','banner2_img','banner3_img','banner4_img','banner5_img','main_img','main2_img','main3_img','main4_img','main5_img','main6_img','desv_img','desv2_img','desv3_img','desv4_img','desv5_img','desv6_img'];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'pwd',
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
        
 