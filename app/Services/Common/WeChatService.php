<?php
namespace App\Services\Common;


use App\Enums\CommonImagesEnum;
use App\Enums\MemberBindEnum;
use App\Repositories\CommonImagesRepository;
use App\Repositories\MemberBindRepository;
use App\Repositories\MemberRelationRepository;
use App\Repositories\MemberBaseRepository;
use App\Services\BaseService;
use EasyWeChat\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Psr\SimpleCache\InvalidArgumentException;
use Tolawho\Loggy\Facades\Loggy;

class WeChatService extends BaseService
{
    /**
     * 微信小程序登录
     * @param $code
     * @return array
     */
    public function miniLogin($code){
        try{
        $config     = config('wechat.mini_program.default');
        $mini       = Factory::miniProgram($config);
        $wx_data    = $mini->auth->session($code);//根据 jsCode 获取用户 session 信息
        $accessToken = $mini->access_token->getToken();
        if (isset($wx_data['errcode'])){
            Loggy::write('error',"v2/WeChatController.php Line:58，Message:$wx_data[errmsg]");
            return ['code'=>0,'message'=>$wx_data['errmsg']];
        }
    }catch (\Exception $e){
        $this->setError($e->getMessage());
        return ['code' => 0];
    } catch (InvalidArgumentException $e) {
            $this->setError($e->getMessage());
            return ['code' => 0];
        }
        return $this->login($code,$wx_data,$accessToken);
    }

    /**
     * 微信小程序绑定手机号
     * @param $request
     * @return array|bool
     */
    public function miniBindMobile($request){
        //获取缓存
        if (Cache::has("miniLogin$request[code]")){
            $wx_data = Cache::get("miniLogin$request[code]");
            //清除缓存
            Cache::forget("miniLogin$request[code]");
        }else{
            return ['code' => 0,'message'=>'请先拉起微信授权'];
        }
        return $this->bindMobile($request['mobile'],$request['referral_code'],$wx_data);
    }


    /**
     * 微信公众号登录
     * @param $code
     * @return array
     */
    public function officialAccountLogin($code)
    {
        try{
            $config         = config('wechat.official_account.default');
            $app            = Factory::officialAccount($config);

            $access_token   = $app->oauth->getAccessToken($code);
            $user           = $app->oauth->user($access_token);
            $user_arr       = $user->getOriginal();
        }catch (\Exception $e){
            $this->setError($e->getMessage());
            return ['code' => 0];
        }
        return $this->login($code,$user_arr,$access_token);
    }

    /**
     * 微信公众号绑定手机号
     * @param $request
     * @return bool
     */
    public function officialAccountBindMobile($request)
    {
        $mobile = $request['mobile'];
        if (!Cache::has("officialAccountLogin$request[code]")){
            $this->setError('请先拉起微信授权！');
            return false;
        }
        $cacheData = Cache::get("officialAccountLogin$request[code]");
        return $this->bindMobile($mobile,$request['referral_code'] ?? '',$cacheData);
    }

    /**
     * 微信登录
     * @param $code
     * @param $user_arr
     * @param $access_token
     * @return array
     */
    public function login($code, $user_arr, $access_token){
        if (!isset($user_arr['openid'])){
            $this->setError('授权失败!');
            return ['code' => 0];
        }
        $openId     = $user_arr['openid'];
        $nickname   = $user_arr['nickname'] ?? '';
        $avatar     = $user_arr['headimgurl'] ?? ($user_arr['avatar'] ?? '');
        $unionid    = $user_arr['unionid'] ?? '';
        if ($bind = MemberBindRepository::getOne(['identifier' => $openId])){
            if (!empty($bind['user_id'])){
                if ($member = MemberBaseRepository::getOne(['id' => $bind['user_id']])){
                    $this->setMessage('登录成功！');
                    return [
                        'code'  => 1,
                        'data'  => [
                            'token' => MemberBaseRepository::getToken($member['id']),
                            'user_info'=> $member
                        ]
                    ];
                }
                $this->setError('登录失败！');
                return ['code' => 0];
            }
        }
        if (empty($bind)){
            $time = time();
            $bind_add = [
                'identity_type' => MemberBindEnum::WECHAT,
                'identifier'    => $openId,
                'credential'    => $access_token,
                'last_login'    => $time,
                'ip_address'    => request()->ip(),
                'additional'    => $unionid,
                'created_at'    => $time
            ];
            if (!MemberBindRepository::getAddId($bind_add)){
                $this->setError('登录失败！');
                return ['code' => 0];
            }
        }
        //缓存信息，用于绑定手机号使用
        $cacheData = [
            'nickname'  => $nickname,
            'avatar'    => $avatar,
            'openid'    => $openId
        ];
        Cache::put("officialAccountLogin$code",$cacheData,3600);
        $this->setMessage('未绑定手机号!');
        return [
            'code' => 2,
            'data' => [
                'token' => '',
                'user_info'=> []
            ]
        ];
    }

    /**
     * 微信绑定手机号
     * @param $mobile
     * @param $referral_code
     * @param $cacheData
     * @return mixed
     */
    public function bindMobile($mobile, $referral_code, $cacheData){
        DB::beginTransaction();
        #如果手机号已经注册，直接进行绑定
        if ($member = MemberBaseRepository::getOne(['mobile' => $mobile])){
            $user_id = $member['id'];
            if (MemberBindRepository::exists(['user_id' => $user_id])){
                $this->setError('手机号已被绑定，请更换手机号！');
                DB::rollBack();
                return false;
            }
        }else{#如果手机号未注册，进行注册，必须要推荐码，创建推荐关系
            if (empty($referral_code)){
                $this->setError('该手机号未注册，需要使用推荐码！');
                DB::rollBack();
                return false;
            }
            if (!$referral_user = MemberBaseRepository::getOne(['referral_code' => $referral_code])){
                $this->setError('无效的推荐码！');
                DB::rollBack();
                return false;
            }
            #获取推荐人的推荐关系，如果不存在，则给推荐人创建一个初始的推荐关系，再进行新用户注册的推荐关系创建
            if (!$relation_user = MemberRelationRepository::getOne(['member_id' => $referral_user['id']])){
                $relation_user = ['parent_id' => 0,'path' => '0,'.$referral_user['id'].',','level' => 1];
                if (!MemberRelationRepository::getAddId($relation_user)){
                    $this->setError('绑定失败！');
                    DB::rollBack();
                    Loggy::write('error','微信绑定手机号，推荐用户推荐关系添加失败！推荐码：'.$referral_user['mobile'].'，手机号：'.$mobile);
                    return false;
                }
            }
            $avatar_id = CommonImagesRepository::getAddId(['type' => CommonImagesEnum::MEMBER,'img_url' => $cacheData['avatar'],'create_at' => time()]);
            if (!$user_id = MemberBaseRepository::addUser($mobile,['avatar_id' => $avatar_id,'ch_name' => $cacheData['nickname']])){
                $this->setError('绑定失败！');
                DB::rollBack();
                Loggy::write('error','微信绑定手机号，新用户创建失败！手机号：'.$mobile);
                return false;
            }
            $relation_data = [
                'member_id'     => $user_id,
                'parent_id'     => $referral_user['id'],
                'path'          => $relation_user['path'] . $user_id . ',',
                'level'         => $relation_user['level'] + 1,
                'created_at'    => time(),
                'updated_at'    => time(),
            ];
            if (!MemberRelationRepository::getAddId($relation_data)){
                $this->setError('绑定失败！');
                DB::rollBack();
                Loggy::write('error','微信绑定手机号，新用户推荐关系创建失败！手机号：'.$mobile);
                return false;
            }
        }
        $bind_upd = ['user_id' => $user_id,'verified_at' => time()];
        if (!MemberBindRepository::getUpdId(['identifier' => $cacheData['openid']],$bind_upd)){
            $this->setError('绑定失败！');
            DB::rollBack();
            return false;
        }
        $this->setMessage('绑定成功！');
        DB::commit();
        return [
            'code'  => 1,
            'data'  => [
                'token'     => MemberBaseRepository::getToken($member['id']),
                'user_info' => $member
            ]
        ];
    }
}
            