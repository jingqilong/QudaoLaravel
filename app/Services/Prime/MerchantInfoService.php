<?php
namespace App\Services\Prime;


use App\Repositories\PrimeMerchantViewRepository;
use App\Services\BaseService;

class MerchantInfoService extends BaseService
{

    /**
     * 获取首页列表
     * @param $request
     * @return mixed
     */
    public function getHomeList($request)
    {
        $type       = $request['type'] ?? null;
        $res['display_url']     = '';
        $res['recommend']       = PrimeMerchantViewRepository::getOneRecommend($type);
        $res['list']            = MerchantService::getHomeList($request);
        $this->setMessage('获取成功！');
        return $res;
    }
}
            