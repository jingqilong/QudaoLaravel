<?php
namespace App\Services\Member;


use App\Repositories\OaMemberRepository;
use App\Services\BaseService;
use App\Services\Common\QiNiuService;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\BaconQrCodeGenerator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
        $referral_code = $user->m_referral_code;
        if (!empty($user->m_referral_qrcode)){
            $this->setMessage('图片获取成功！');
            return ['url' => $user->m_referral_qrcode];
        }
        if (empty($user->m_referral_code)){
            $referral_code = OaMemberRepository::getReferralCode();
            if (!OaMemberRepository::getUpdId(['m_id' => $user->m_id],['m_referral_code' => $referral_code])){
                $this->setError('图片获取失败！');
                return false;
            }
        }
        $image_name = 'qr_code'.$user->m_id;//dd(unlink($image_name));
        $img_path = $this->buildQrCode('http://qudaoplus.cc/?referral_code='.$referral_code, $image_name);
        $qiniuService = new QiNiuService();
        if (!$res = $qiniuService->uploadImages('Member',$image_name.$user->m_id,$img_path)){
            $this->setError('图片获取失败！');
            return false;
        }
        OaMemberRepository::getUpdId(['m_id' => $user->m_id],['m_referral_qrcode' => $res['url']]);
        unlink($img_path);
        $this->setMessage('图片获取成功！');
        return ['url' => $res['url']];
    }

    /**
     * 生成二维码
     * @param $url
     * @param $name
     * @return mixed
     */
    public function buildQrCode($url,$name){
        $qrcode = new BaconQrCodeGenerator;
        $qrcode
            ->format('png')
            ->size(300)
            ->errorCorrection('H')
            ->backgroundColor(255,249,177)//香槟色
            ->merge('/public/logo.png',.3)
            ->generate($url, '../public/'.$name.'.png');
        return public_path($name.'.png');
    }
}
            