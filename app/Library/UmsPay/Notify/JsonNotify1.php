<?php


namespace App\Library\UmsPay\Notify;


class JsonNotify1
{
    public function doPost($request,$response)
    {
        if (empty($request['mac']) || empty($request['context'])){
            return false;
        }
        //验证签名，对收到的原始报文和秘钥进行md5加密
    }
}