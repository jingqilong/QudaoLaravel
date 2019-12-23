<?php


namespace App\Library\YiKaYi;

/*
 * 会员营销系统开放平台 SKD V1.0.0
 * 此 SDK 适用于 PHP 5 及其以上版本。
 * 文本编码方式UTF-8
 * lib文件夹为SDK核心文件夹，包含了对OpenAPI接口的请求和数据的接收，OpenApiClient.php文件为核心文件
 *
 * LastupDate 2016-05-16
 */

use Ixudra\Curl\Facades\Curl;

class YiKaYiSms {
    private $OpenId;
    private $Secret;
    private $xml;

    public function __construct() {
        // OpenId、Secret在平台获取
        $this->OpenId = '80D02F3AFAD2425DA70A28550275E04F';
        $this->Secret = "6BWPQ9";
    }

    public function userAccount() {
        $account = "10000";
        echo $account;
    }

    /**
     * GET发送请求
     * @param $url
     * @param $data
     * @return mixed
     */
    private function getData($url, $data) {
        $curl=Curl::to($url)
            ->withOption('CONNECTTIMEOUT',300)
            ->withData($data)
            ->asJson(true);

        //通过GET方式提交
        $result = $curl->get();
        return $result;
    }

    public function CallHttpPost($action, $data) {
        if (empty ( $action )) {
            return array (
                "status" => - 1,
                "message" => "方法名不能为空！"
            );
        }

        if (! isset ( $data ) || count ( $data ) == 0) {
            return array (
                "status" => - 1,
                "message" => "请求参数不能为空！"
            );
        }

        $xmlPath = dirname ( __FILE__ ) . "/OpenApiData.xml";

        if (! isset ( $this->xml )) {
            if (! file_exists ( $xmlPath )) {
                return array (
                    "status" => - 1,
                    "message" => "API配置文件不存在！"
                );
            }
            $this->xml = simplexml_load_file ( $xmlPath );
        }

        if(empty($this->xml)){
            return array(
                "status" => -1,
                "message"=> "一卡易载入失败"
            );
        }

        $actionNode = $this->xml->xpath ( "//Action[@name='" . $action . "']" );
        if ($actionNode == null) {
            return array (
                "status" => - 1,
                "message" => "未能找到" . $action . "方法！"
            );
        }

        $controllerName = $actionNode [0]->attributes ();
        $controllerName = $controllerName["controller"];
        $json_data = json_encode ( $data );
        $TimeStamp = time ();
        $Signature = strtoupper ( md5 ( $this->OpenId . $this->Secret . $TimeStamp . $json_data ) );
        $url = "http://openapi.1card1.cn/" . $controllerName . "/" . $action;// . "?openId=" . $this->OpenId . "&signature=" . $Signature . "&timestamp=" . $TimeStamp;
        $getData['data'] = $json_data;
        $getData['openId'] = $this->OpenId;
        $getData['signature'] = $Signature;
        $getData['timestamp'] = $TimeStamp;
        return $this->getData ( $url, $getData );
    }
}