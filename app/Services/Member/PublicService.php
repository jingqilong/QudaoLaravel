<?php
namespace App\Services\Member;


use App\Repositories\MemberBaseRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\BaconQrCodeGenerator;

class PublicService extends BaseService
{
    protected $auth;

    /**
     * EmployeeService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }

    /**
     * 获取推广二维码图
     * @return bool|mixed
     */
    public function getQrCode()
    {
        $user = $this->auth->user();
        $referral_code = $user->referral_code;
        if (empty($referral_code)){
            $referral_code = MemberBaseRepository::getReferralCode();
            if (!MemberBaseRepository::getUpdId(['id' => $user->id],['referral_code' => $referral_code])){
                $this->setError('您还没有邀请码！');
                return false;
            }
        }
        $url        = config('url.'.env('APP_ENV').'_url').'?referral_code='.$referral_code;
        $image_path = public_path('qrcode'.DIRECTORY_SEPARATOR.$referral_code.'.png');
        $res = [
            'url'           => $url,
            'referral_code' => $referral_code,
            'qrcode_url'    => url('qrcode'.DIRECTORY_SEPARATOR.$referral_code.'.png')
        ];
        if (file_exists($image_path)){
            $this->setMessage('获取成功！');
            return $res;
        }
        if (false == $this->buildQrCode($url, $image_path)){
            $this->setError('生成失败！');
            return false;
        }
        return $res;
    }

    /**
     * 获取获取测试二维码，用于给外部人员测试使用
     * @return array|bool
     */
    public function getTestQrCode()
    {
        $referral_code = config('common.test_referral_code');
        $url        = config('url.'.env('APP_ENV').'_url').'?referral_code='.$referral_code;
        $image_path = public_path('qrcode'.DIRECTORY_SEPARATOR.$referral_code.'.png');
        $res = [
            'url'           => $url,
            'referral_code' => $referral_code,
            'qrcode_url'    => url('qrcode'.DIRECTORY_SEPARATOR.$referral_code.'.png')
        ];
        if (file_exists($image_path)){
            $this->setMessage('获取成功！');
            return $res;
        }
        if (false == $this->buildQrCode($url, $image_path)){
            $this->setError('生成失败！');
            return false;
        }
        return $res;
    }

    public function buildQrCode($url, $image_path){
        $qr_code = new BaconQrCodeGenerator();
        $qr_code->format('png')
            ->size(300)
            ->margin(1)
            ->errorCorrection('M')
            ->generate($url, $image_path);
        $this->setMessage('获取成功！');
        if (!file_exists($image_path)){
            return false;
        }
        return true;
    }
}
            