<?php
namespace App\Services\Common;


use App\Enums\CommonImagesEnum;
use App\Enums\MemberBindEnum;
use App\Enums\MemberEnum;
use App\Enums\MemberIsTestEnum;
use App\Repositories\CommonImagesRepository;
use App\Repositories\MemberBindRepository;
use App\Repositories\MemberGradeRepository;
use App\Repositories\MemberRelationRepository;
use App\Repositories\MemberBaseRepository;
use App\Services\BaseService;
use App\Services\Member\MemberService;
use App\Services\Member\RelationService;
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
            Loggy::write('error'," App\Services\Common\WeChatService.php Line:34，Message:$wx_data[errmsg]");
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
                    //测试用户检查,如果测试时间已过，则登录失败
                    $memberService = new MemberService();
                    if (false == $memberService->checkTestUser($member['id'])){
                        $this->setError($memberService->error,$memberService->code);
                        return ['code' => 0];
                    }
                    $this->setMessage('登录成功！');
                    if (!$grade = MemberGradeRepository::getOne(['user_id' => $bind['user_id'],'status' => 1,'end_at' => ['notIn',[1,time()]]])){
                        $grade = MemberEnum::DEFAULT;
                    }
                    Loggy::write('error','成员等级打印信息',$grade);
                    $member['grade']        = $grade;
                    $member['grade_title']  = MemberEnum::getGrade($grade,'普通成员');
                    $member['sex']          = MemberEnum::getSex($member['sex']);
                    $member                 = ImagesService::getOneImagesConcise($member,['avatar_id' => 'single']);
                    Loggy::write('error','成员信息打印',$member);
                    unset($member['avatar_id'],$member['status'],$member['hidden'],$member['created_at'],$member['updated_at'],$member['deleted_at']);
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
        //用户头像ID
        $avatar_id          = CommonImagesRepository::getAddId(['type' => CommonImagesEnum::MEMBER,'img_url' => $cacheData['avatar'],'create_at' => time()]);
        //如果用户是通过测试推荐码进来的，设置用户为测试用户
        $test_referral_code = config('common.test_referral_code');
        $user_info = [
            'avatar_id'     => $avatar_id,
            'ch_name'       => $cacheData['nickname'],
            'is_test'       => $test_referral_code == $referral_code ? MemberIsTestEnum::TEST : MemberIsTestEnum::NO_TEST,
            'updated_at'    => time()
        ];
        $relationService = new RelationService();
        #如果手机号已经注册，直接进行绑定
        if ($member = MemberBaseRepository::getOne(['mobile' => $mobile])){
            $user_id = $member['id'];
            if (MemberBindRepository::exists(['user_id' => $user_id])){
                $this->setError('手机号已被绑定，请更换手机号！');
                DB::rollBack();
                return false;
            }
            //如果此手机号已经注册过，且是测试账户，如果现在是正常注册，需要刷新账户状态为非注册，并且更新账户推荐关系
            if (MemberIsTestEnum::TEST == $member['is_test'] && MemberIsTestEnum::NO_TEST == $user_info['is_test']){
                if (!MemberBaseRepository::getUpdId(['id' => $user_id],$user_info)){
                    DB::rollBack();
                    $this->setError('绑定失败！');
                    Loggy::write('error','微信绑定手机号，用户信息更新失败！手机号：'.$mobile);
                    return false;
                }
                //更新推荐关系
                if (false == $relationService->updateRelation($user_id,$referral_code)){
                    DB::rollBack();
                    $this->setError('绑定失败！');
                    Loggy::write('error','微信绑定手机号，更新新用户推荐关系失败！手机号：'.$mobile);
                    return false;
                }
            }else{
                //测试用户检查,如果测试时间已过，则绑定失败
                $memberService = new MemberService();
                if (false == $memberService->checkTestUser($user_id)){
                    $this->setError($memberService->error,$memberService->code);
                    DB::rollBack();
                    return false;
                }
            }
        }else{#如果手机号未注册，进行注册，必须要推荐码，创建推荐关系
            if (empty($referral_code)){
                $this->setError('该手机号未注册，需要使用推荐码！');
                DB::rollBack();
                return false;
            }
            if (!$user_id = MemberBaseRepository::addUser($mobile,$user_info)){
                $this->setError('绑定失败！');
                DB::rollBack();
                Loggy::write('error','微信绑定手机号，新用户创建失败！手机号：'.$mobile);
                return false;
            }
            $member = MemberBaseRepository::find($user_id);
            //创建推荐关系
            if (false == $relationService->createdRelation($user_id,$referral_code)){
                $this->setError($relationService->error);
                Loggy::write('error','微信绑定手机号，新用户推荐关系创建失败！手机号：'.$mobile.'，错误信息：'.$relationService->error);
                DB::rollBack();
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
        if (!$grade = MemberGradeRepository::getField(['user_id' => $user_id,'status' => 1,'end_at' => ['notIn',[1,time()]]],'grade')){
            $grade = MemberEnum::DEFAULT;
        }
        $member['grade']        = $grade;
        $member['grade_title']  = MemberEnum::getGrade($grade,'普通成员');
        $member['sex']          = MemberEnum::getSex($member['sex']);
        $member                 = ImagesService::getOneImagesConcise($member,['avatar_id' => 'single']);
        unset($member['avatar_id'],$member['status'],$member['hidden'],$member['created_at'],$member['updated_at'],$member['deleted_at']);
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
            