<?php
namespace App\Services\Common;


use App\Enums\SMSEnum;
use App\Library\YiKaYi\YiKaYiSms;
use App\Repositories\CommonSmsRepository;
use App\Repositories\MemberBaseRepository;
use App\Repositories\OaEmployeeRepository;
use App\Repositories\PrimeMerchantRepository;
use App\Services\BaseService;
use Tolawho\Loggy\Facades\Loggy;

class SmsService extends BaseService
{

    public $module = [
        'member' => SMSEnum::MEMBERLOGIN,
    ];

    /**
     * 登录短信验证
     * @param string $mobile    手机号
     * @param string $module    模块名
     * @param string $code      验证码
     * @return bool|string
     */
    public function loginCheck($mobile, $module, $code){
        if (!$this->loginCheckUser($mobile,$module)){
            return '您还没有注册，请先去注册后再登录！';
        }

        $check_sms = $this->checkCode($mobile,$this->module[$module], $code);
        if (is_string($check_sms)){
            return $check_sms;
        }
        return true;
    }


    /**
     * 验证码验证
     * @param $mobile
     * @param $type
     * @param $code
     * @return bool|string
     */
    public function checkCode($mobile, $type, $code)
    {
        if (!$sms = CommonSmsRepository::getOrderOne(['mobile' => $mobile,'type' => $type,'status' => 0], 'created_at')){
            Loggy::write("debug","短信验证码： 没有找到有效的短信记录！",['input_code' => $code,'mobile' => $mobile,'type' => $type]);
            return '短信已过期，请重新获取！';
        }
        $time = time();
        $sms_ttl = config('common.sms.ttl',300);
        if ($time > ($sms['created_at'] + $sms_ttl)){
            CommonSmsRepository::getUpdId(['id' => $sms['id']], ['status' => 1]);
            Loggy::write("debug","短信验证码：短信已过期！",['send_code' => $sms['code'],'input_code' => $code,'mobile' => $mobile,'type' => $type,'status' => 0,'sms_time' => $sms['created_at'],'now_time' => $time]);
            return '短信已过期，请重新获取！';
        }
        if ($code != $sms['code']){
            return '验证码有误，请重新输入！';
        }
        CommonSmsRepository::getUpdId(['id' => $sms['id']], ['status' => 1]);
        return true;
    }


    /**
     * 登录前手机号检查
     * @param $mobile
     * @param $module
     * @return bool|mixed
     */
    public function loginCheckUser($mobile, $module){
        $res = false;
        switch ($module){
            case 'member':
                $res = MemberBaseRepository::exists(['mobile' => $mobile]);
                break;
            case 'prime':
                $res = PrimeMerchantRepository::exists(['mobile' => $mobile]);
                break;
            case 'oa':
                $res = OaEmployeeRepository::exists(['mobile' => $mobile]);
                break;
        }
        return $res;
    }

    /**
     * 发送短信
     * @param $mobile
     * @param $type
     * @return array
     */
    public function sendCode($mobile, $type)
    {
        if (!SMSEnum::exists($type)){
            return ['code' => 0, 'message' => '暂无此类型！'];
        }
        if (!SMSEnum::isRegister($type)){//如果不是注册类，需要检验手机号是否存在
            if (!$module = SMSEnum::getModule($type)){
                return ['code' => 0, 'message' => '此短信类型未设置模块！'];
            }

            if ($type != SMSEnum::BINDMOBILE)
            if (!$this->loginCheckUser($mobile, $module)){
                return ['code' => 0, 'message' => '手机号未注册，不能发送短信！'];
            }
        }
        $sms_ttl = config('common.sms.ttl',300);
        $sms_long = config('common.sms.length',4);
        if (CommonSmsRepository::exists(['mobile' => $mobile, 'type' => $type, 'status' => 0, 'created_at' => ['>=', time() - $sms_ttl]])){
            return ['code' => 0, 'message' => '验证码已发送，请勿重复操作！'];
        }
        $code = '';
        for ($i=0;$i < $sms_long;$i++){
            $code .= rand(0,9);
        }
        $content = sprintf(SMSEnum::getTemplate($type),$code);
        //TODO  此处发送短信
        $yiKaYi = new YiKaYiSms();
        $data = array (
            "userAccount" => "10000",
            "mobile" => $mobile,
            "content" => $content
        );
        $res = $yiKaYi->CallHttpPost('SendSms',$data);
        if ($res['status'] != 0){
            Loggy::write('error','短信发送失败！|内容：'.$content.' |手机号：'.$mobile.' |失败原因：'.$res['message'].' |错误码：'.$res['status']);
            return ['code' => 0, 'message' => '短信发送失败，请重试！'];
        }
        CommonSmsRepository::getAddId([
            'type'      => $type,
            'mobile'    => $mobile,
            'code'      => $code,
            'title'     => SMSEnum::getLabel($type),
            'content'   => $content,
            'status'    => 0,
            'created_at' => time(),
        ]);
        return ['code' => 1, 'message' => '短信发送成功！'];
    }

    /**
     * 给用户发送自定义短信
     * @param $mobile
     * @param $content
     * @return array
     */
    public function sendContent($mobile, $content){
        $yiKaYi = new YiKaYiSms();
        $data = array (
            "userAccount"   => "10000",
            "mobile"        => $mobile,
            "content"       => $content
        );
        $res = $yiKaYi->CallHttpPost('SendSms',$data);
        if ($res['status'] != 0){
            Loggy::write('error','短信发送失败！|内容：'.$content.' |手机号：'.$mobile.' |失败原因：'.$res['message'].' |错误码：'.$res['status']);
            return ['code' => 0, 'message' => '短信发送失败，请重试！'];
        }
        CommonSmsRepository::getAddId([
            'mobile'    => $mobile,
            'title'     => SMSEnum::getLabel(0),
            'content'   => $content,
            'created_at' => time(),
        ]);
        return ['code' => 1, 'message' => '短信发送成功！'];
    }
}
            