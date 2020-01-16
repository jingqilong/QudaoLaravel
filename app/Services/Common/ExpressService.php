<?php
namespace App\Services\Common;


use App\Repositories\CommonExpressRepository;
use App\Repositories\ShopOrderRelateRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Cache;
use Ixudra\Curl\Facades\Curl;

class ExpressService extends BaseService
{


    /**
     * Oa根据订单号获取物流状态
     * @param $code
     * @param $number
     * @return bool
     */
    public function getOaOrderExpressDetails($code, $number)
    {
        if (!$expressInfo = CommonExpressRepository::getOne(['code' => $code,'status' => 1])){
            $this->setError('快递公司不存在!');
            return false;
        }
        if (!ShopOrderRelateRepository::exists(['express_number' => $number])){
            $this->setError('快递单号不存在!');
            return false;
        }
        $expressDetail = ExpressService::getExpressDetails($code, $number);
        if ($expressDetail['message'] != 'ok'){
            $this->setError($expressDetail['message']);
            return false;
        }
        $result['express_name'] = $expressInfo['company_name'];
        $result['express_number'] = empty($expressDetail['nu']) ? '' : $expressDetail['nu'];
        $result['ischeck'] = $expressDetail['ischeck'] == 0 ? '未签收' : '已签收';
        $result['data'] = $expressDetail['data'];
        $this->setMessage('获取成功!');
        return $result;
    }

    /**
     * OA获取快递列表
     * @return bool|null
     */
    public function getExpressList()
    {
        if (!$list = CommonExpressRepository::getList(['id' => ['>',1],'status' => 1],['*'],'id','asc')){
            $this->setError('获取失败!');
            return false;
        }
        $this->setMessage('获取成功!');
        return $list;
    }

    /**
     * 获取快递公司物流信息
     * @param $code
     * @param $number
     * @return mixed
     */
    protected function getExpressDetails($code, $number)
    {
        $cache_key = md5($code . $number);
        if (Cache::has($cache_key)){
            return Cache::get($cache_key);
        }
        $key = env('EXPRESS_KEY');				//客户授权key
        $customer = env('EXPRESS_CUSTOMER');	//查询公司编号
        $param = array (
            'com' => $code,			//快递公司编码
            'num' => $number,	    //快递单号
            'resultv2' => '1'	    //开启行政区域解析
        );
        //请求参数
        $post_data = array();
        $post_data["customer"] = $customer;
        $post_data["param"] = json_encode($param);
        $sign = md5($post_data["param"].$key.$post_data["customer"]);
        $post_data["sign"] = strtoupper($sign);
        $url = 'http://poll.kuaidi100.com/poll/query.do';	//实时查询请求地址
        $params = "";
        foreach ($post_data as $k=>$v) {
            $params .= "$k=".urlencode($v)."&";		//默认UTF-8编码格式
        }
        $post_data = substr($params, 0, -1);
        $result = Curl::to($url)->withData($post_data)->asJsonResponse(true)->post();

        Cache::add($cache_key,$result,7200);
        return $result;
    }
}
            