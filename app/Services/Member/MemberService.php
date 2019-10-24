<?php
namespace App\Services\Member;


use App\Enums\MemberEnum;
use App\Repositories\MedicalDepartmentRepository;
use App\Repositories\MemberBindRepository;
use App\Repositories\MemberRelationRepository;
use App\Repositories\MemberRepository;
use App\Repositories\OaAdminPermissionsRepository;
use App\Repositories\OaAdminRolesRepository;
use App\Services\BaseService;
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
        $account_type = 'm_num';
        if (preg_match($mobile_regex, $account)) {
            $account_type = 'm_phone';
        }
        if (preg_match($email_regex, $account)) {
            $account_type = 'm_email';
        }
        if (!MemberRepository::exists([$account_type => $account])){
            return '用户不存在！';
        }
        $token = MemberRepository::login([$account_type => $account, 'password' => $password]);
        if (is_array($token)){
            return $token['message'];
        }
        $user = $this->auth->user();
        return ['user' => $user, 'token' => $token];
    }


    /**
     * 手机号注册
     * @param $data
     * @return mixed
     */
    public function register($data)
    {
        if (MemberRepository::exists(['m_phone' => $data['mobile']])){
            $this->setError('该手机号码已注册过!');
            return false;
        }
        $referral_code = $data['referral_code'] ?? '';
        //添加用户
        DB::beginTransaction();
        if (!$user_id = MemberRepository::addUser($data['mobile'])) {
            DB::rollBack();
            $this->setError('注册失败!');
            Loggy::write('error', '手机号注册创建用户失败，手机号：' . $data['mobile'] . '  推荐人推荐码：' . $referral_code);
            return false;
        }
        //建立用户推荐关系
        $relation_data['member_id'] = $user_id;
        $relation_data['created_at'] = time();
        if (empty($referral_code)) {
            $relation_data['parent_id'] = 0;
            $relation_data['path'] = '0,' . $user_id . ',';
            $relation_data['level'] = 1;
        } else {
            if (!$referral_user = MemberRepository::getOne(['m_referral_code' => $referral_code])) {
                DB::rollBack();
                $this->setError('无效的推荐码!');
                return false;
            }
            if (!$relation_user = MemberRelationRepository::getOne(['member_id' => $referral_user['m_id']])) {
                $relation_user = [
                    'member_id'     => $referral_user['m_id'],
                    'parent_id'     => 0,
                    'path'          => '0,' . $referral_user['m_id'] . ',',
                    'level'         => 1,
                    'created_at'    => time(),
                    'updated_at'    => time(),
                ];
                if (!MemberRelationRepository::getAddId($relation_user)){
                    DB::rollBack();
                    $this->setError('注册失败!');
                    Loggy::write('error', '手机号注册创建推荐关系失败，推荐人id：' . $referral_user['m_id'] . '  推荐人推荐码：' . $referral_code);
                    return false;
                }
            }
            $relation_data['parent_id'] = $referral_user['m_id'];
            $relation_data['path'] = $relation_user['path'] . $user_id . ',';
            $relation_data['level'] = $relation_user['level'] + 1;
        }
        if (!MemberRelationRepository::getAddId($relation_data)) {
            DB::rollBack();
            Loggy::write('error', '推荐关系建立失败，用户id：' . $relation_data['m_id'] . '  推荐人id：' . $relation_data['parent_id']);
            $this->setError('注册失败!');
            return false;
        }
        $token = MemberRepository::getToken($user_id);
        $user_info = MemberRepository::getUser();
        DB::commit();

        $this->setMessage('注册成功');
        return ['user' => $user_info->toArray(),'token' => $token];
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
        if ($token = MemberRepository::refresh($token)){
            return $token;
        }
        return false;
    }

    /**
     * 成员按条件查找排序
     * @param $data
     * @return array|bool|null
     */
    public function getMemberList($data)
    {
        $memberInfo = $this->auth->user();

        $page           = $data['page'] ?? 1;
        $asc            = $data['asc'] ==  1 ? 'asc' : 'desc';
        $page_num       = $data['page_num'] ?? 20;
        $keywords       = $data['keywords'] ?? null;
        $column         = ['m_id','m_num','m_ename','m_groupname','m_workunits','m_time','m_category'];
        $where          = ['m_starte' => ['in',[MemberEnum::ACTIVITEMEMBER,MemberEnum::ACTIVITEOFFICER]]];
        $groupMember    = [
            MemberEnum::HONOURENJOY,
            MemberEnum::YUEENJOY,
            MemberEnum::ADVISER,
            MemberEnum::ALSOENJOY,
            MemberEnum::TOENJOY,
            MemberEnum::REALLYENJOY,
            MemberEnum::YOUENJOY
        ];
        $keyword        = [$keywords => ['m_cname','m_ename','m_category','m_num','m_phone']];

        if (in_array($memberInfo['m_groupname'],$groupMember)){
            if(!$user_list = MemberRepository::search($keyword,$where,$column,$page,$page_num,'m_time',$asc)){
                $this->setMessage('暂无成员信息！');
                return [];
            }
        }else {
            if (!$user_list = MemberRepository::search($keyword,['m_starte' => MemberEnum::ACTIVITEMEMBER],$column,$page,$page_num,'m_time',$asc)){
                $this->setMessage('暂无成员信息！');
                return [];
            }
        }

        unset($user_list['first_page_url'], $user_list['from'],
              $user_list['last_page_url'],  $user_list['from'],
              $user_list['next_page_url'],  $user_list['path'],
              $user_list['prev_page_url'],  $user_list['to']);

        foreach ($user_list['data'] as &$value){
            $value['group_name']        = empty($value['m_groupname']) ? '' : MemberEnum::getGrade($value['m_groupname']);
            $value['category_name']     = empty($value['m_category']) ? '' : MemberEnum::getCategory($value['m_category']);
            $value['starte']            = empty($value['m_starte']) ? '' : MemberEnum::getStatus($value['m_starte']);
            $value['sex']               = empty($value['m_sex']) ? '' : MemberEnum::getSex($value['m_sex']);
        }

            $this->setMessage('获取成功！');
            return $user_list;
    }

    /**
     * 根据成员分类获取成员列表
     * @param $data
     * @return array|null
     */
    public function getMemberCategoryList($data)
    {
        $memberInfo = $this->auth->user();
        $page           = $data['page'] ?? 1;
        $asc            = $data['asc'] ==  1 ? 'asc' : 'desc';
        $page_num       = $data['page_num'] ?? 20;
        $column         = ['m_id','m_num','m_ename','m_groupname','m_workunits','m_time','m_category'];
        $where          = ['m_category' => $data['category'],'m_starte' => ['in',[MemberEnum::ACTIVITEMEMBER,MemberEnum::ACTIVITEOFFICER]]];
        $groupMember    = [
            MemberEnum::HONOURENJOY,
            MemberEnum::YUEENJOY,
            MemberEnum::ADVISER,
            MemberEnum::ALSOENJOY,
            MemberEnum::TOENJOY,
            MemberEnum::REALLYENJOY,
            MemberEnum::YOUENJOY
        ];

        if (in_array($memberInfo['m_groupname'],$groupMember)){
            if(!$user_list = MemberRepository::getList($where,$column,'m_time',$asc,$page,$page_num)){
                $this->setMessage('暂无成员信息！');
                return [];
            }
        }else {
            if (!$user_list = MemberRepository::getList(['m_category' => $data['category'],'m_starte' => MemberEnum::ACTIVITEMEMBER],$column,'m_time',$asc,$page,$page_num)){
                $this->setMessage('暂无成员信息！');
                return [];
            }
        }

        unset($user_list['first_page_url'], $user_list['from'],
              $user_list['last_page_url'],  $user_list['from'],
              $user_list['next_page_url'],  $user_list['path'],
              $user_list['prev_page_url'],  $user_list['to']);

        foreach ($user_list['data'] as &$value){
            $value['group_name']        = empty($value['m_groupname']) ? '' : MemberEnum::getGrade($value['m_groupname']);
            $value['category_name']     = empty($value['m_category']) ? '' : MemberEnum::getCategory($value['m_category']);
            $value['starte']            = empty($value['m_starte']) ? '' : MemberEnum::getStatus($value['m_starte']);
            $value['sex']               = empty($value['m_sex']) ? '' : MemberEnum::getSex($value['m_sex']);
        }

        $this->setMessage('获取成功！');
        return $user_list;
    }

    /**
     * Get user info.
     * @return mixed
     */
    public function getUserInfo(){
        if ($user = MemberRepository::getUser()){
            return $user;
        }
        return false;
    }

    /**
     * 微信小程序登录，逻辑代码
     * @param $request
     * @return array
     */
    public function miniLogin($request){
        try {
            $config = config('wechat.mini_program.default');
            $mini = Factory::miniProgram($config);
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
                //return ['code'=>0,'message'=>'获取unionid失败,请重试'];
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
            $mini = Factory::miniProgram($config);
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
     * 手机号直接登录
     * @param $mobile
     * @return array
     */
    public function mobileLogin($mobile)
    {
        if (!$user = MemberRepository::getOne(['m_phone' => $mobile])){
            return ['code' => 0, 'message' => '您还没有注册，请先去注册后再登录！'];
        }
        $token = MemberRepository::getToken($user['m_id']);
        return ['code' => 1, 'message' => '登录成功！', 'data' => ['token' => $token, 'user' => $user]];
    }

    /**
     * 会员修改密码
     * @param array $data
     * @return array
     */
    public function changePassword(array $data)
    {
        //$password = Hash::make($data['m_password']);
        $upd_repwd = Hash::make($data['m_repwd']);
        /*if ($resd = MemberRepository::getFields(['m_id' => $data['m_id']])){dd($resd);
            return ['code' => 1,'message' => '原始密码不正确！'];
        }*/
        if (!$res = MemberRepository::getUpdId(['m_id' => $data['m_id']],['m_password' => $upd_repwd])){
            return ['code' => 0,'message' => '修改失败！'];
        }
        return ['code' => 1,'message' => '恭喜您，修改成功！'];
    }

    /**
     * 会员验证码修改密码
     * @param array $data
     * @return array
     */
    public function smsChangePassword(array $data)
    {
        unset($data['m_repwd']);
        $upd_repwd = Hash::make($data['m_password']);
        if (!$res = MemberRepository::getUpdId(['m_phone' => $data['m_phone']],['m_password' => $upd_repwd])){
            return ['code' => 0,'message' => '修改失败！'];
        }
        return ['code' => 1,'message' => '恭喜您，修改成功！'];
    }

    /**
     * @param $type
     * @return array|bool|null
     */
    public function getRelationList($type){
        $user = $this->auth->user();
        if ($type == 1){
            $relation_list = MemberRelationRepository::doubleRelation($user->m_id);
        }else{
            $relation_list = MemberRelationRepository::detailRelation($user->m_id);
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
     * 检测手机号是否注册
     * @param $mobile
     * @return mixed
     */
    public function mobileExists($mobile)
    {
        $this->setMessage('查询成功！');
        return MemberRepository::exists(['m_phone' => $mobile]);
    }
    /**
     * 手机号码注册完善用户信息
     * @param array $data
     * @return bool|null
     */
    public function perfectMemberInfo(array $data)
    {
        if (!$member = MemberRepository::getOne(['m_phone' => $data['m_phone']])){
            $this->setError('手机号码不一致呦！');
            return false;
        }
        unset($data['m_phone'], $data['sign']);
        $data['m_groupname'] = MemberEnum::TOAUDIT;
        $data['m_starte'] = MemberEnum::DISABLEMEMBER;
        $data['m_time'] = date('Y-m-d H:m:s',time());
        if (!$memberInfo = MemberRepository::getUpdId(['m_id' => $member['m_id']],$data)){
            $this->setError('信息完善失败，请重试！');
            return false;
        }

        $this->setMessage('信息完善成功!');
        return $memberInfo;
    }
}
