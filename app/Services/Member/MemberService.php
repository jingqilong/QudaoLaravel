<?php
namespace App\Services\Member;


use App\Enums\MemberEnum;
use App\Enums\ScoreEnum;
use App\Enums\ShopOrderEnum;
use App\Repositories\CommonImagesRepository;
use App\Repositories\MemberBaseRepository;
use App\Repositories\MemberBindRepository;
use App\Repositories\MemberGradeRepository;
use App\Repositories\MemberGradeViewRepository;
use App\Repositories\MemberInfoRepository;
use App\Repositories\MemberPersonalServiceRepository;
use App\Repositories\MemberRelationRepository;
use App\Repositories\MemberRepository;
use App\Repositories\MemberSignRepository;
use App\Repositories\MemberSpecifyViewRepository;
use App\Repositories\ScoreRecordRepository;
use App\Repositories\ShopOrderRelateRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
use App\Services\Score\RecordService;
use App\Traits\HelpTrait;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Exceptions\HttpException;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use EasyWeChat\Kernel\Exceptions\RuntimeException;
use EasyWeChatComposer\Exceptions\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tolawho\Loggy\Facades\Loggy;

class MemberService extends BaseService
{
    use HelpTrait;
    protected $auth;

    /**
     * MemberService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }

    /**
     * 用户登录，返回用户信息和TOKEN，可使用会员卡号、手机号、邮箱登录
     * @param $account
     * @param $password
     * @return mixed|string
     */
    public function login($account, $password){
        //兼容用户名登录、手机号登录、邮箱登录
        $mobile_regex = '/^(1(([35789][0-9])|(47)))\d{8}$/';
        $email_regex  = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
        $account_type = 'card_no';
        if (preg_match($mobile_regex, $account)) {
            $account_type = 'mobile';
        }
        if (preg_match($email_regex, $account)) {
            $account_type = 'email';
        }
        if (!MemberBaseRepository::exists([$account_type => $account])){
            return '用户不存在！';
        }
        $token = MemberBaseRepository::login([$account_type => $account, 'password' => $password]);
        if (is_array($token)){
            return $token['message'];
        }
        $user           = $this->auth->user();
        $user           = $user->toArray();
        $user['sex']    = MemberEnum::getSex($user['sex']);
        $user           = ImagesService::getOneImagesConcise($user,['avatar_id' => 'single']);
        unset($user['avatar_id'],$user['status'],$user['hidden'],$user['created_at'],$user['updated_at'],$user['deleted_at']);
        return ['user' => $user, 'token' => $token];
    }


    /**
     * 手机号注册
     * @param $data
     * @return mixed
     */
    public function register($data)
    {
        if (MemberBaseRepository::exists(['mobile' => $data['mobile']])){
            $this->setError('该手机号码已注册过!');
            return false;
        }
        $referral_code = $data['referral_code'] ?? '';
        //添加用户
        DB::beginTransaction();
        if (!$user_id = MemberBaseRepository::addUser($data['mobile'])) {
            DB::rollBack();
            $this->setError('注册失败!');
            Loggy::write('error', '手机号注册创建用户失败，手机号：' . $data['mobile'] . '  推荐人推荐码：' . $referral_code);
            return false;
        }
        //建立用户推荐关系
        $relation_data['member_id']     = $user_id;
        $relation_data['created_at']    = time();
        if (empty($referral_code)) {
            $relation_data['parent_id'] = 0;
            $relation_data['path']      = '0,' . $user_id . ',';
            $relation_data['level']     = 1;
        } else {
            if (!$referral_user = MemberBaseRepository::getOne(['referral_code' => $referral_code])) {
                DB::rollBack();
                $this->setError('无效的推荐码!');
                return false;
            }
            if (!$relation_user = MemberRelationRepository::getOne(['member_id' => $referral_user['id']])) {
                $relation_user = [
                    'member_id'     => $referral_user['id'],
                    'parent_id'     => 0,
                    'path'          => '0,' . $referral_user['id'] . ',',
                    'level'         => 1,
                    'created_at'    => time(),
                    'updated_at'    => time(),
                ];
                if (!MemberRelationRepository::getAddId($relation_user)){
                    DB::rollBack();
                    $this->setError('注册失败!');
                    Loggy::write('error', '手机号注册创建推荐关系失败，推荐人id：' . $referral_user['id'] . '  推荐人推荐码：' . $referral_code);
                    return false;
                }
            }
            $relation_data['parent_id'] = $referral_user['id'];
            $relation_data['path']      = $relation_user['path'] . $user_id . ',';
            $relation_data['level']     = $relation_user['level'] + 1;
        }
        if (!MemberRelationRepository::getAddId($relation_data)) {
            DB::rollBack();
            Loggy::write('error', '推荐关系建立失败，用户id：' . $user_id . '  推荐人id：' . $relation_data['parent_id']);
            $this->setError('注册失败!');
            return false;
        }
        $token          = MemberBaseRepository::getToken($user_id);
        $user_info      = MemberBaseRepository::getUser();
        $user           = $user_info->toArray();
        $user['sex']    = MemberEnum::getSex($user['sex']);
        $user           = ImagesService::getOneImagesConcise($user,['avatar_id' => 'single']);
        unset($user['avatar_id'],$user['status'],$user['hidden'],$user['created_at'],$user['updated_at'],$user['deleted_at']);
        DB::commit();

        $this->setMessage('注册成功');
        return ['user' => $user,'token' => $token];
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @param $token
     * @return bool
     */
    public function logout($token)
    {
        if (MemberRepository::logout($token)){
            return true;
        }
        return false;
    }

    /**
     * Refresh a token.
     *
     * @param $token
     * @return mixed
     */
    public function refresh($token){
        try {
            if ($token = MemberRepository::refresh($token)){
                $this->setMessage('刷新成功！');
                return $token;
            }
            $this->setError('刷新失败！');
            return false;
        }catch (\Exception $e){
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * 成员按条件查找排序  (拆表后  已修改)
     * @param $data
     * @return array|bool|null
     */
    public function getMemberList($data)
    {
        if (empty($data['asc'])){
            $data['asc']  = 1;
        }
        $member       =   $this->auth->user();
        $member_info  =   MemberGradeViewRepository::getOne(['id' => $member->id,'deleted_at' => 0,'hidden' => 0]);
        $keywords     =   $data['keywords'] ?? null;
        $page         =   $data['page'] ?? 1;
        $page_num     =   $data['page_num'] ?? 20;
        $asc          =   $data['asc'] == 1 ? 'asc' : 'desc';
        $where        =   ['deleted_at' => 0 ,'hidden' => 0];
        if (MemberEnum::TEMPORARY == $member_info['grade']){
            $where['status'] =  MemberEnum::MEMBER;
        }
        $column = ['id','ch_name','img_url','grade','title','category','status','created_at'];
        if (!empty($keywords)){
            $keyword  = [$keywords => ['ch_name','category','mobile']];
            if(!$list = MemberGradeViewRepository::search($keyword,$where,$column,$page,$page_num,'created_at',$asc)){
                $this->setError('获取失败!');
                return false;
            }
        }else {
            if (!$list = MemberGradeViewRepository::getList($where,$column,'created_at',$asc,$page,$page_num)){
                $this->setError('获取失败!');
                return false;
            }
        }
        $list  = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据!');
            return $list;
        }
        foreach ($list['data'] as $key => &$value){
            $value['grade']      =   MemberEnum::getGrade($value['grade'],$value['grade']);
            $value['category']   =   MemberEnum::getGrade($value['category'],$value['category']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 成员查看成员信息 (拆表后  已修改)
     * @param $request
     * @return array|bool
     */
    public function getMemberInfo($request){
        $base_column  = ['id','ch_name','avatar_id','status'];
        $info_column  = ['employer','position','title','profile'];
        $member_base  = MemberBaseRepository::getOne(['id' => $request['id']],$base_column);
        $member_info  = MemberInfoRepository::getOne(['member_id' => $request['id']],$info_column);
        $member_data  = array_merge($member_base,$member_info);
        $member_data  = ImagesService::getOneImagesConcise($member_data,['avatar_id' => 'single']);
        if ($member_data['status'] == MemberEnum::DISABLEMEMBER  && $member_data['status'] == MemberEnum::DISABLEOFFICER){
            $this->setError('该成员已被禁用');
            return false;
        }
        foreach ($member_data as &$value){
            $value = $value ?? '';
        }
        $member_data['profile'] = strip_tags($member_data['profile']);
        $this->setMessage('获取成功!');
        return $member_data;
    }


    /**
     * 根据成员分类获取成员列表 (拆表后  已修改)
     * @param $data
     * @return bool|mixed|null
     */
    public function getMemberCategoryList($data)
    {
        if (empty($data['asc'])){
            $data['asc'] = 1;
        }
        $member       =   $this->auth->user();
        $member_info  =   MemberGradeViewRepository::getOne(['id' => $member->id,'deleted_at' => 0]);
        $category     =   $data['category'] ?? null;
        $page         =   $data['page'] ?? 1;
        $page_num     =   $data['page_num'] ?? 20;
        $asc          =   $data['asc'] == 1 ? 'asc' : 'desc';
        $where        =   ['deleted_at' => 0 ];
        if (!empty($category)){
            $where['category'] = $category;
         }
        if (MemberEnum::HONOURENJOY == $member_info['grade']){
            $where['status'] = ['in',[MemberEnum::ACTIVITEMEMBER,MemberEnum::ACTIVITEOFFICER]];
        }else{
            $where['status'] =  MemberEnum::ACTIVITEMEMBER;
        }
        $column = ['id','ch_name','img_url','grade','title','category','created_at'];
        if (!$list = MemberGradeViewRepository::getList($where,$column,'created_at',$asc,$page,$page_num)) {
            $this->setError('获取失败!');
            return false;
        }
        $list  = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        foreach ($list['data'] as &$value){
           $value['grade']      = MemberEnum::getGrade($value['grade'],$value['grade']);
           $value['category']   = MemberEnum::getCategory($value['category'],$value['category']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 获取自己的成员信息 (拆表后  已修改)
     * Get user info.
     * @return mixed
     */
    public function getMemberInfoByUser(){
        $user = $this->auth->user();
        $member_id = $user->id;
        $info_column            = ['grade','employer','title','industry','brands','category','position','profile','birthday','address'];
        $base_column            = ['card_no','ch_name','mobile','email','sex','avatar_id'];
        $member_info            = MemberInfoRepository::getOne(['member_id' => $member_id],$info_column);
        $member_base            = MemberBaseRepository::getOne(['id' => $member_id],$base_column);
        $member                 = array_merge($member_base,$member_info);
        $member['sex_name']     = MemberEnum::getSex($member['sex'],$member['sex']);
        $member                 = ImagesService::getOneImagesConcise($member,['avatar_id' => 'single']);
        $member['grade']        = MemberEnum::getGrade($member['grade'],$member['grade']);
        $member['category']     = MemberEnum::getCategory($member['category'],$member['category']);
        $member['profile']      = strip_tags($member['profile']);
        $member['birthday']     = date('Y-m-d',strtotime($member['birthday']));
        foreach ($member as &$value){
            $value = $value ?? '';
        }
        $this->setMessage('获取成功!');
        return $member;
    }

    /**
     * 微信小程序登录，逻辑代码
     * @param $request
     * @return array
     */
    public function miniLogin($request){
        try {
            $config = config('wechat.mini_program.default');
            $mini   = Factory::miniProgram($config);
            $wx_data = $mini->auth->session($request['code']);//根据 jsCode 获取用户 session 信息
            if (isset($wx_data['errcode'])){
                Loggy::write('error',"v2/WeChatController.php Line:58，Message:$wx_data[errmsg]");
                return ['code'=>0,'message'=>$wx_data['errmsg']];
            }
            //验签
            if ($request['signature'] != sha1($request['raw_data'].$wx_data['session_key'])){
                return ['code' => 0, 'message' => '微信签名不一致'];
            }
            $accessToken = $mini->access_token->getToken();
            $accessToken = (array)$accessToken;
            if (isset($accessToken['errcode'])){
                return ['code' => 0, 'message' => $accessToken['errmsg']];
            }
            //解密获取微信用户信息
            $decrypt_data = $mini->encryptor->decryptData($wx_data['session_key'], $request['iv'], $request['encrypted_data']);
            //return ['code'=>1,'message'=>'success','data'=>['decrypt_data'=>$decrypt_data,'wx_data'=>$wx_data]];
            if (!isset($wx_data['unionid'])){
                Loggy::write('error',"v2/WeChatController.php Line:70,message:获取unionid失败,请重试");
                return ['code'=>0,'message'=>'获取unionid失败,请重试'];
            }
            $result = $this->miniWechatLogin($wx_data['openid'],$accessToken['access_token'],isset($wx_data['unionid'])?$wx_data['unionid']:0);
        } catch (DecryptException $e) {
            Loggy::write('error',"File:".$e->getFile()."Line".$e->getLine()."Message:".$e->getMessage());
            return ['code'=>0,'message'=>$e->getMessage()];
        } catch (InvalidConfigException $e) {
            Loggy::write('error',"File:".$e->getFile()."Line".$e->getLine()."Message:".$e->getMessage());
            return ['code'=>0,'message'=>$e->getMessage()];
        } catch (HttpException $e) {
            Loggy::write('error',"File:".$e->getFile()."Line".$e->getLine()."Message:".$e->getMessage());
            return ['code'=>0,'message'=>$e->getMessage()];
        } catch (InvalidArgumentException $e) {
            Loggy::write('error',"File:".$e->getFile()."Line".$e->getLine()."Message:".$e->getMessage());
            return ['code'=>0,'message'=>$e->getMessage()];
        } catch (RuntimeException $e) {
            Loggy::write('error',"File:".$e->getFile()."Line".$e->getLine()."Message:".$e->getMessage());
            return ['code'=>0,'message'=>$e->getMessage()];
        } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
            Loggy::write('error',"File:".$e->getFile()."Line".$e->getLine()."Message:".$e->getMessage());
            return ['code'=>0,'message'=>$e->getMessage()];
        } catch (\EasyWeChat\Kernel\Exceptions\DecryptException $e) {
            Loggy::write('error',"File:".$e->getFile()."Line".$e->getLine()."Message:".$e->getMessage());
            return ['code'=>0,'message'=>$e->getMessage()];
        } catch (\Exception $e){
            Loggy::write('error',"File:".$e->getFile()."Line".$e->getLine()."Message:".$e->getMessage());
            return ['code'=>0,'message'=>$e->getMessage()];
        }

        //缓存session_key和openid
        $cacheData['miniLogin'] = [
            'session_key' => $wx_data['session_key'],
            'openid'      => $wx_data['openid'],
            'nickname'    => isset($wx_data['nickName']) ? $wx_data['nickName'] : '渠道PLUS',
            'avatar'      => isset($wx_data['avatarUrl']) ? $wx_data['avatarUrl'] : '',
        ];

        if ($result['code'] == 1){//登录成功
            if (isset($result['data']['token'])){
                $data = [
                    'union_id'      => isset($wx_data['unionid'])?$wx_data['unionid']:0,
                    'wx_user_info'  => $decrypt_data,
                    'sys_user_info' => $result['data']['user_info'],
                    'token'         => $result['data']['token']
                ];
                return ['code' => 1,'message' => '登录成功','data' => $data];
            }else{
                return ['code' => 0,'message' => '登录异常，请重试'];
            }
        }

        if($result['code'] == 2) {//未绑定手机号
            //缓存信息，用于绑定手机号使用
            Cache::forever("miniLogin$request[code]",$cacheData);
            $arr = [
                'union_id'      => isset($wx_data['unionid']) ? $wx_data['unionid'] : 0,
                'wx_user_info'  => $decrypt_data,
                'sys_user_info' => [],
                'token'         => ''
            ];
            return ['code' => 2, 'message' => '需要绑定用户', 'data' => $arr];
        }
        return ['code' => 0 , 'message' => $result['message']];
    }


    /**
     * 小程序微信登录更新用户信息
     * @param string $open_id
     * @param string $access_token
     * @param string $union_id
     * @return array
     */
    public function miniWechatLogin($open_id,$access_token,$union_id='0'){
        //如果用户不存在
        if (!MemberBindRepository::exists(['identifier' => $open_id])){
            //查询用户是否使用微信登录过
            if (!$user_id = MemberRepository::getField(['m_openids' => $open_id],'m_id')){
                //创建微信用户
                if ($w_user_id = MemberBindRepository::createWeChatUser($open_id,$access_token,$union_id)){
                    return ['code'=>2, 'message' => '用户未绑定', 'data'=>['wechat_user_id'=>$w_user_id]];
                }
                return ['code' => 0, 'message' => '微信用户创建失败'];
            }
            if (!$w_user_id = MemberBindRepository::createWeChatUser($open_id,$union_id,$user_id)){
                Loggy::write('error','微信用户创建失败, 用户ID:'.$user_id.' openid:'.$open_id);
            }
            if (! $token = MemberRepository::getToken($user_id)) {
                Loggy::write('error','token获取失败, 用户ID'.$user_id);
                return ['code' => 0,'message' => 'token获取失败'];
            }
            return [
                'code'=>1,
                'message' => '登录成功',
                'data'=>[
                    'token'=>$token,
                    'user_info'=> $this->auth->user()
                ]
            ];

        }
        //如果用户已存在
        //获取用户
        $userObj    = $this->getUserByWechatOpenId($open_id);
        $w_user_id  = -1;
        if ($userObj['status'] == 0){
            //微信首次登录，用户不存在，创建用户
            $w_user_id = MemberBindRepository::createWeChatUser($open_id,$union_id);
        }
        if ($userObj['status'] == 1){
            //微信注册登录过，未绑定手机号(系统用户)，
            $w_user_id = $userObj['wuser']['id'];
        }
        if ($userObj['status'] <= 1){
            return ['code' => 2, 'message' => '未绑定手机号', 'data' => ['wechat_user_id' => $w_user_id]];
        }
        $user = $userObj['user'];
        //更新微信用户
        MemberBindRepository::getUpdId(['user_id' => $user['m_id']],
            [   'credential' => $access_token,
                'last_login' => time(),
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);
        if (! $token = MemberRepository::getToken($user['m_id'])) {
            Loggy::write('error','token获取失败, 用户ID'.$user['m_id']);
            return ['code' => 0, 'message' => 'token获取失败'];
        }
        return [
            'code'      => 1,
            'message'   => '登录成功',
            'data'      => [
                'token' => $token,
                'user_info'=> $this->auth->user()
            ]
        ];
    }


    /**
     * 使用open_id 获取用户信息
     * @param $open_id
     * @return array
     */
    public function getUserByWechatOpenId($open_id){
        $row = MemberBindRepository::getOne(['identifier' => $open_id]);
        if (empty($row)){//微信登录信息未存储
            return ['status' => 0, 'message' => '登录出错，请重试！','user' => ''];
        }
        $user_id = $row['user_id'];
        if ($row['user_id'] == 0){//用户未绑定
            return ['status' => 1, 'message' => '用户未绑定', 'wuser' => $row];
        }
        $user = MemberRepository::getOne(['m_id' => $user_id]);
        if (!empty($user)){//已绑定且已获取用户信息
            return ['status' => 2,'message' => '已获取用户信息', 'user' => $user ];
        }
        //用户信息未找到
        return ['status' => 3, 'message' => '数据异常','user' => ''];
    }

    /**
     * 微信小程序手机绑定接口,逻辑代码
     * @param $request
     * @return array
     */
    public function miniBindMobile($request){
        try {
            $config = config('wechat.mini_program.default');
            $mini   = Factory::miniProgram($config);
            //获取缓存中的session_key和openid
            if (Cache::has("miniLogin$request[code]")){
                $wx_data = Cache::get("miniLogin$request[code]");
                //清除缓存
                Cache::forget("miniLogin$request[code]");
            }else{
                return ['code' => 0,'message'=>'请先拉起微信授权'];
            }
            //解密获取微信用户信息
            $decrypt_data = $mini->encryptor->decryptData($wx_data['miniLogin']['session_key'], $request['iv'], $request['encrypted_data']);
            if (isset($decrypt_data['purePhoneNumber'])){
                $phoneNumber = $decrypt_data['purePhoneNumber'];
            }else{
                return ['code' => 0,'message' => '获取手机号失败,请重试'];
            }
            //同步信息
//            $phoneNumber = '18156253871';
//            $open_id = 'oyhXx0LkRDQOi6yiBvJNdXFgmMJ0';
//            $decrypt_data = [
//                'nickName' => 'hahah',
//                'avatarUrl'   => '1324546546'
//            ];
            $m_img = $wx_data['miniLogin']['avatar'];

            $promo_code = null;
            if (isset($request['promo_code'])){
                $promo_code = $request['promo_code'];
            }
            $res_data = $this->bindMobile($phoneNumber,$wx_data['miniLogin']['openid'],$m_img,$promo_code);
        } catch (DecryptException $e) {
            Loggy::write('error',"File:".$e->getFile()."Line".$e->getLine()."Message:".$e->getMessage());
            return ['code'=>0,'message'=>$e->getMessage()];
        } catch (InvalidConfigException $e) {
            Loggy::write('error',"File:".$e->getFile()."Line".$e->getLine()."Message:".$e->getMessage());
            return ['code'=>0,'message'=>$e->getMessage()];
        }catch (\Exception $e){
            return ['code'=>0,'message'=>$e->getMessage()];
        }
        if ($res_data['code'] == 0){
            return ['code'=>0,'message'=> $res_data['message']];
        }
        $user_info = MemberRepository::getOne(['m_phone'=>$phoneNumber]);
        return [
            'code' => 1,
            'message' => '手机号绑定成功',
            'data' => [
                'sys_user_info' => $user_info,
                'token' => $res_data['token']
            ]
        ];
    }

    /**
     * 小程序绑定手机号
     * @param $mobile
     * @param $openid
     * @param $m_img
     * @param $promo_code
     * @return array|bool|JsonResponse
     */
    public function bindMobile($mobile, $openid,$m_img,$promo_code){
        //检查注册用户
        $res = $this->checkOrRegister($mobile,$promo_code,$m_img);
        if (is_string($res)){
            return ['code' => 0, 'message' => $res];
        }
        if (MemberBindRepository::getOne(['user_id' => $res['m_id']])){
            return ['code' => 0, 'message' => '手机号已被绑定'];
        }
        $token = MemberRepository::getToken($res['m_id']);
        $bind_info = ['user_id' => $res['m_id'],'last_login' => time(),'verified_at' => time(), 'ip_address' => $_SERVER['REMOTE_ADDR']];
        if (MemberBindRepository::getUpdId(['identifier' => $openid], $bind_info)){//
            return ['code' => 1, 'message' => '手机号绑定成功', 'token' => $token];
        }
        return ['code' => 0, 'message' => '手机号绑定失败'];
    }

    /**
     * 检查用户是否存在，不存在则创建用户
     * @param $mobile
     * @param $referral_code
     * @param $m_img
     * @return string|array  返回错误信息或用户信息
     */
    public function checkOrRegister($mobile, $referral_code,$m_img){
        //查找用户
        if ($user = MemberRepository::getOne(['m_phone' => $mobile])){
            return $user;
        }
        //添加用户
        DB::beginTransaction();
        if (!$user_id = MemberRepository::addUser($mobile,['m_img' =>$m_img])){
            DB::rollBack();
            return '用户创建失败！';
        }
        //建立用户推荐关系
        $relation_data['member_id'] = $user_id;
        $relation_data['created_at'] = time();
        if (empty($referral_code)){
            $relation_data['parent_id'] = 0;
            $relation_data['path'] = '0,'.$user_id.',';
            $relation_data['level'] = 1;
        }else{
            if (!$referral_user = MemberRepository::getOne(['m_referral_code' => $referral_code])){
                DB::rollBack();
                return '无效的推荐码';
            }
            if (!$relation_user = MemberRelationRepository::getOne(['member_id' => $referral_user['m_id']])){
                DB::rollBack();
                Loggy::write('error','用户推荐关系丢失，用户id：'.$user_id.'  推荐人推荐码：'.$referral_code);
                return '数据异常';
            }
            $relation_data['parent_id'] = $referral_user['m_id'];
            $relation_data['path'] = $relation_user['path'] . $user_id . ',';
            $relation_data['level'] = $relation_user['level'] + 1;
        }
        if (!MemberRelationRepository::getAddId($relation_data)){
            DB::rollBack();
            Loggy::write('error','推荐关系建立失败，用户id：'.$user_id.'  推荐人id：'.$relation_data['parent_id']);
            return '推荐关系建立失败';
        }
        DB::commit();
        return MemberRepository::find($user_id);
    }


    /**
     * 手机号直接登录 (拆表后  已修改)
     * @param $mobile
     * @return array|bool
     */
    public function mobileLogin($mobile)
    {
        if (!$user = MemberBaseRepository::getOne(['mobile' => $mobile])){
            $this->setError('您还没有注册，请先去注册后再登录!');
            return false;
        }
        $token      = MemberBaseRepository::getToken($user['id']);
        $this->setMessage('登录成功!');
        $user['sex']    = MemberEnum::getSex($user['sex']);
        $user           = ImagesService::getOneImagesConcise($user,['avatar_id' => 'single']);
        unset($user['avatar_id'],$user['status'],$user['hidden'],$user['created_at'],$user['updated_at'],$user['deleted_at']);
        $results    = ['token' => $token, 'user' => $user];
        return $results;
    }

    /**
     * 会员修改密码 (拆表后  已修改)
     * @param array $data
     * @return bool
     */
    public function changePassword(array $data)
    {
        $member        = $this->auth->user();
        $member_id     = $member->id;
        $old_password  = $member->password;
        if (!MemberBaseRepository::exists(['id' => $member_id,'deleted_at' => 0])){
            $this->setError('没有该成员信息!');
            return false;
        }
        $upd_arr = [
            'password'      => Hash::make($data['repwd']),
            'updated_at'    => time(),
        ];
        if (!Hash::check($data['password'],$old_password)){
            $this->setError('密码错误哦!');
            return false;
        }
        if (!MemberBaseRepository::getUpdId(['id' => $member_id],$upd_arr)){
            $this->setError('修改密码失败!');
            return false;
        }
        $this->setMessage('修改成功!');
        return true;
    }

    /**
     * 会员验证码修改密码  (拆表后  已修改)
     * @param array $request
     * @return bool
     */
    public function smsChangePassword(array $request)
    {
        if (!$request['password'] == $request['repwd']){
            $this->setError('密码不一致!');
            return false;
        }
        if (!$member = MemberBaseRepository::getOne(['mobile' => $request['mobile']])){
            $this->setError('获取失败!');
        }
        $upd_arr = [
            'password'      => Hash::make($request['password']),
            'mobile'        => $request['mobile'],
            'updated_at'    => time(),
        ];
        if (!MemberBaseRepository::getUpdId(['id' => $member['id']],$upd_arr)){
            $this->setError('修改失败,请重试!');
            return false;
        }
        $this->setMessage('恭喜您，修改成功!');
        return true;
    }

    /**
     * @param $type
     * @return array|bool|null
     */
    public function getRelationList($type){
        $user = $this->auth->user();
        if ($type == 1){
            $relation_list = MemberRelationRepository::doubleRelation($user->id);
        }else{
            $relation_list = MemberRelationRepository::detailRelation($user->id);
        }
        if ($relation_list === false){
            $this->setError('推荐关系获取失败！');
            return false;
        }
        if (!$relation_list){
            $this->setMessage('您还没有推荐过人！');
            return [];
        }
        $this->setMessage('获取推荐关系成功！');
        return $relation_list;
    }

    /**
     * 检测手机号是否注册 (拆表后  已修改)
     * @param $mobile
     * @return mixed
     */
    public function mobileExists($mobile)
    {
        if (MemberBaseRepository::exists(['mobile' => $mobile])){
            $this->setMessage('已被注册!');
            return ['is_register' => 1];
        }
        $this->setMessage('未被注册！');
        return ['is_register' => 0];
    }



    /**
     * 手机号码注册完善用户信息  (拆表后  已修改)
     * @param array $request
     * @return bool|null
     */
    public function perfectMemberInfo($request)
    {
        if (!$member = MemberBaseRepository::getOne(['mobile' => $request['m_phone']])){
            $this->setError('手机号码不一致呦！');
            return false;
        }
        $base_arr = [
            'id'         => $member['id'],
            'ch_name'    => $request['cname'],
            'sex'        => $request['sex'],
            'email'      => $request['email'],
        ];
        $info_arr = [
            'member_id'      => $member['id'],
            'birthday'       => $request['birthday'],
            'id_card'        => is_null($request['numcard']) ?? '' ,
            'address'        => is_null($request['address']) ?? '' ,
            'info_provider'  => is_null($request['referrername']) ?? '',
        ];
        $service_arr = [
            'publicity'     =>  is_null($request['wechattext']) ?? '',
            'services'      =>   is_null($request['services']) ?? '',
        ];
        DB::beginTransaction();
        if (!MemberBaseRepository::getUpdId(['id' => $member['id']],$base_arr)){
            DB::rollBack();
            $this->setError('信息完善失败，请重试！');
            return false;
        }
        if (!MemberInfoRepository::getUpdId(['member_id' => $member['id']],$info_arr)){
            DB::rollBack();
            $this->setError('信息完善失败，请重试！');
            return false;
        }
        if (!MemberPersonalServiceRepository::getUpdId(['member_id' => $member['id']],$service_arr)){
            DB::rollBack();
            $this->setError('信息完善失败，请重试！');
            return false;
        }
        DB::commit();
        $this->setMessage('信息完善成功!');
        return true;
    }

    /**
     * 获取首页展示成员
     * @param $count
     * @return array
     */
    public static function getHomeShowMemberList($count){
        if (!$list = MemberSpecifyViewRepository::getList(['user_id' => 0])){
            return [];
        }
        $view_user_ids  = array_column($list,'view_user_id');
        $column         = ['id','ch_name','img_url','grade','title','category','status','created_at'];
        if (!$view_user_list = MemberGradeViewRepository::getList(['id' => ['in',$view_user_ids]],$column,'id','asc',1,$count)){
            return [];
        }
        if (empty($view_user_list['data'])){
            return [];
        }
        return $view_user_list['data'];
    }

    /**
     * 成员编辑个人信息  (拆表后  已修改)
     * @param $request
     * @return bool
     */
    public function editMemberInfo($request)
    {
        $member    = $this->auth->user();
        $member_id = $member->id;
        if (!MemberBaseRepository::getOne(['id' => $member_id,'deleted_at' => 0])){
            $this->setError('成员不存在!');
            return false;
        }
        $base_arr = [
            'mobile'     => $request['m_phone'],
            'sex'        => $request['m_sex'],
            'email'      => $request['m_email'],
        ];
        $info_arr = [
            'member_id'  => $member_id,
            'birthday'   => $request['m_birthday'] ?? null,
            'employer'   => $request['m_workunits'] ?? null,
            'industry'   => $request['m_industry'] ?? null,
            'address'    => $request['m_address'] ?? null,
            'title'      => $request['m_socialposition'] ?? null,
            'profile'    => $request['m_introduce'] ?? null
        ];
        if (MemberBaseRepository::exists($base_arr)){
            $this->setError('成员已存在!');
            return false;
        }
        DB::beginTransaction();
        if (!MemberBaseRepository::getUpdId(['id' => $member_id],$base_arr)){
            DB::rollBack();
            $this->setError('修改失败!');
            return false;
        }
        if (!MemberInfoRepository::getUpdId(['member_id' => $member_id],$info_arr)){
            DB::rollBack();
            $this->setError('修改失败!');
            return false;
        }
        DB::commit();
        $this->setMessage('修改成功!');
        return true;

    }

    /**
     * 获取成员人数
     * @return array|bool
     */
    public function getUserCount()
    {
        $res = [];
        $res[] = [
            'value' => MemberGradeRepository::count(['status' => 1]),
            'name'  => '全部会员'
        ];
        $where = [
            MemberEnum::TEST          => ['grade' => MemberEnum::TEST       ,  'status' => 1 ],
            MemberEnum::ALSOENJOY     => ['grade' => MemberEnum::ALSOENJOY  ,  'status' => 1 ],
            MemberEnum::TOENJOY       => ['grade' => MemberEnum::TOENJOY    ,  'status' => 1 ],
            MemberEnum::YUEENJOY      => ['grade' => MemberEnum::YUEENJOY   ,  'status' => 1 ],
            MemberEnum::REALLYENJOY   => ['grade' => MemberEnum::REALLYENJOY,  'status' => 1 ],
            MemberEnum::YOUENJOY      => ['grade' => MemberEnum::YOUENJOY   ,  'status' => 1 ],
            MemberEnum::HONOURENJOY   => ['grade' => MemberEnum::HONOURENJOY,  'status' => 1 ],
            MemberEnum::ZHIRENJOY     => ['grade' => MemberEnum::ZHIRENJOY  ,  'status' => 1 ],
            MemberEnum::ADVISER       => ['grade' => MemberEnum::ADVISER    ,  'status' => 1 ],
            MemberEnum::TEMPORARY     => ['grade' => MemberEnum::TEMPORARY  ,  'status' => 1 ],
        ];
        foreach ($where as $key => $value){
            $res[] = [
                'value' => MemberGradeRepository::count($value),
                'name'  => MemberEnum::getGrade($key),
            ];
        }
        $this->setMessage('获取成功!');
        return $res;
    }

    /**
     * 个人中心
     * @return array
     */
    public function personalCenter()
    {
        $member = $this->auth->user();
        $res    = [
            'is_sign'       => 0,
            'total_score'   => 0
        ];
        //获取会员等级
        $res['member_grade'] = '普通成员';
        if ($grade = MemberGradeRepository::getOne(['user_id' => $member->id,'status' => 1])){
            if(!empty($grade['end_at']) && $grade['end_at'] < time()){
                $res['member_grade'] = '普通成员';
            }else{
                $res['member_grade'] = MemberEnum::getGrade($grade['grade'],'普通成员');
            }
        }
        if ($sign = MemberSignRepository::exists(['member_id' => $member->id,'sign_at' => strtotime(date('Y-m-d'))])){
            $res['is_sign'] = 1;
        }
        $res['total_score'] = ScoreRecordRepository::sum(['member_id' => $member->id,'latest' => ScoreEnum::LATEST],'remnant_score') ?? 0;
        //待付款
        $res['trading']     = ShopOrderRelateRepository::exists(['member_id' => $member->id,'status' => ShopOrderEnum::PAYMENT]) ? 1 : 0;
        //待发货
        $res['ship']        = ShopOrderRelateRepository::exists(['member_id' => $member->id,'status' => ShopOrderEnum::SHIP]) ? 1 : 0;
        //待收货
        $res['shipped']     = ShopOrderRelateRepository::exists(['member_id' => $member->id,'status' => ShopOrderEnum::SHIPPED]) ? 1 : 0;
        //待评价
        $res['received']    = ShopOrderRelateRepository::exists(['member_id' => $member->id,'status' => ShopOrderEnum::RECEIVED]) ? 1 : 0;
        $this->setMessage('获取成功！');
        return $res;
    }

    /**
     * 每日签到
     * @return bool
     */
    public function sign()
    {
        $member = $this->auth->user();
        if (MemberSignRepository::exists(['member_id' => $member->id,'sign_at' => strtotime(date('Y-m-d'))])){
            $this->setError('今天已经签到过了,明天再来吧！');
            return false;
        }
        $add_arr = [
            'member_id' => $member->id,
            'sign_at'   => strtotime(date('Y-m-d')),
            'sign_score'=> 1
        ];
        DB::beginTransaction();
        if (!MemberSignRepository::getAddId($add_arr)){
            $this->setError('签到失败！');
            DB::rollBack();
            return false;
        }
        $RecordService = new RecordService();
        //赠送金币积分
        if (!$RecordService->increaseScore(3,1,$member->id,'每日签到','每日签到',false)){
            $this->setError('签到失败！');
            DB::rollBack();
            return false;
        }
        $this->setMessage('签到成功！');
        DB::commit();
        return true;
    }

    /**
     * 签到页详情
     * @return array
     */
    public function signDetails()
    {
        $member = $this->auth->user();
        $str_time = 'HI,';
        $hour = date('H');
        if ($hour >= 4 && $hour < 9){
            $str_time .= '早上好';
        }
        if ($hour > 9 && $hour < 12){
            $str_time .= '上午好';
        }
        if ($hour >= 12 && $hour < 14){
            $str_time .= '中午好';
        }
        if ($hour >= 14 && $hour < 18){
            $str_time .= '下午好';
        }
        if ($hour >= 18 && $hour < 24){
            $str_time .= '下午好';
        }
        if ($hour >= 0 && $hour < 4){
            $str_time .= '夜深了，早点休息';
        }
        $res = [
            'title'         => $str_time,
            'get_score'     => 1,
            'total_score'   => MemberSignRepository::sum(['member_id' => $member->id],'sign_score') ?? 0,
            'is_sign'       => MemberSignRepository::exists(['member_id' => $member->id,'sign_at' => strtotime(date('Y-m-d'))]) ? 1 : 0
        ];
        $this->setMessage('获取成功！');
        return $res;
    }
}
