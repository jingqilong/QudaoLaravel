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
     * @param $url
     * @param $size
     * @return bool|mixed
     */
    public function getQrCode($url,$size)
    {
        $user = $this->auth->user();
        $referral_code = $user->referral_code;
        if (empty($referral_code)){
            $this->setError('您还没有邀请码！');
            return false;
        }
        try{
            $qr_code = $this->buildQrCode($url.'?referral_code='.$referral_code.'&time='.time(),$size);
            $this->setMessage('图片获取成功！');
            return $qr_code;
        }catch (\Exception $e){
            $this->setError('图片获取失败！');
            return false;
        }

//
//
//        if (!empty($user->m_referral_qrcode)){
//            $this->setMessage('图片获取成功！');
//            return ['url' => $user->m_referral_qrcode];
//        }
//        if (empty($user->m_referral_code)){
//            $referral_code = OaMemberRepository::getReferralCode();
//            if (!OaMemberRepository::getUpdId(['m_id' => $user->id],['m_referral_code' => $referral_code])){
//                $this->setError('图片获取失败！');
//                return false;
//            }
//        }
//        $image_name = 'qr_code'.$user->id;//dd(unlink($image_name));
//        $img_path = $this->buildQrCode('http://qudaoplus.cc/?referral_code='.$referral_code, $image_name);
//        $qiniuService = new QiNiuService();
//        if (!$res = $qiniuService->uploadImages('Member',$image_name.$user->id,$img_path)){
//            $this->setError('图片获取失败！');
//            return false;
//        }
//        OaMemberRepository::getUpdId(['m_id' => $user->id],['m_referral_qrcode' => $res['url']]);
//        unlink($img_path);
//        $this->setMessage('图片获取成功！');
//        return ['url' => $res['url']];
    }

    /**
     * 生成二维码
     * @param $url
     * @param $size
     * @return mixed
     */
    public function buildQrCode($url,$size = 200){
        $qrcode = QrCode::size($size)
            ->errorCorrection('L')
            ->generate($url);
        return $qrcode;
    }
}
            