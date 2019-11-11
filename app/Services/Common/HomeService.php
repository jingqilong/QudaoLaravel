<?php
namespace App\Services\Common;


use App\Services\Activity\DetailService;
use App\Services\BaseService;
use App\Services\Member\MemberService;
use App\Services\Shop\ActivityService;
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

    /**
     * 首页展示数据获取
     * @return mixed
     */
    public function getHome()
    {
        #获取banner图
        $res['banners']     = HomeBannersService::getHomeBanners();
        #获取积分展示图
        $res['score']       = ActivityService::getHomeShow();
        #获取推荐精选活动
        $res['activities']  = app(DetailService::class)->getHomeList(['page_num' => 4,'is_recommend' => 1]);
        #获取成员风采
        $res['members']     = MemberService::getHomeShowMemberList(8);
        #获取好物推荐
        $res['shop']        = [];
//        $res['shop']        = ActivityService::getHomeRecommendGoods();
        $this->setMessage('获取成功！');
        return $res;
    }
}
            