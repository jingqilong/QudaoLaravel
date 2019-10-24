<?php
namespace App\Services\Common;


use App\Repositories\CommonHomeBannersRepository;
use App\Repositories\CommonImagesRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;

class HomeService extends BaseService
{
    public $auth;

    /**
     * CollectService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }

    public function getHome()
    {
        $member = $this->auth->user();
//        if ($member){
//            $this->setMessage('获取成功！');
//            return $member->toArray();
//        }
        #获取banner图
        $res['banners']     = HomeBannersService::getHomeBanners();
        #获取积分展示图
        $res['score']       = ['id' => 2,'image' => ''];
        #获取推荐精选活动
        $res['activities']  = [];
        #获取成员风采
        $res['members']     = [];
        #获取好物推荐
        $res['shop']        = [];
        $this->setMessage('获取成功！');
        return $res;
    }
}
            