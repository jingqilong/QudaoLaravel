<?php
namespace App\Services\Common;


use App\Repositories\CommonServiceTermsRepository;
use App\Services\BaseService;

class CommonServiceTermsService extends BaseService
{


    /**
     * 用户获取渠道平台服务条款
     * @param $request
     * @return bool
     */
    public function getCommonServiceTerms($request)
    {
        if (!$commonTerms = CommonServiceTermsRepository::getOne(['type' => $request['type']])){
            $this->setError('获取失败!');
            return false;
        }
        $this->setMessage('获取成功!');
        return $commonTerms;
    }

    /**
     * 添加渠道平台服务条款类型值
     * @param $request
     * @return bool
     */
    public function addCommonServiceTerms($request)
    {
        if (CommonServiceTermsRepository::exists(['type' => $request['type']])){
            $this->setError('您添加的服务类型已存在!');
            return false;
        }
        $add_arr = [
            'type'       => $request['type'],
            'value'      => $request['value'],
            'create_at'  => time(),
            'updated_at' => time()
        ];
        if (!CommonServiceTermsRepository::getAddId($add_arr)){
            $this->setError('您添加的服务类型已存在!');
            return false;
        }
        $this->setMessage('添加成功!');
        return true;
    }
}