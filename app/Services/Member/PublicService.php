<?php
namespace App\Services\Member;


use App\Repositories\OaMemberRepository;
use App\Services\BaseService;
use App\Services\Common\QiNiuService;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageCache;
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
        $referral_code = $user->referral_code;
        if (empty($referral_code)){
            $this->setError('您还没有邀请码！');
            return false;
        }
        $url        = config('url.'.env('APP_ENV').'_url').'?referral_code='.$referral_code;
        $image_path = public_path('qrcode\\'.$referral_code.'.png');
        $res = [
            'referral_code' => $referral_code,
            'qrcode_url'    => url('qrcode/'.$referral_code.'.png')
        ];
        if (file_exists($image_path)){
            $this->setMessage('获取成功！');
            return $res;
        }
        QrCode::format('png')
            ->size(300)
            ->margin(1)
            ->errorCorrection('M')
            ->generate($url, $image_path);
        $this->setMessage('获取成功！');
        return $res;
    }
}
            