<?php

namespace App\Library\UmsPay\Notify;


class JsonNotify
{


protected function doPost($request, $response){
$request.setCharacterEncoding("UTF-8");

$context = request->getParameter("context");
$mac = $request->getParameter("mac");
System.out.println("大华异步通知的报文context是："+context);
System.out.println("大华异步通知的签名mac是："+mac);
if($context==null || "".equals(context)){
response.getWriter().write("An empty message was received.");
System.out.println("收到了空的报文");
return ;//有可能存在通知报文没有参数的情况，例如验证商户系统是否正常
}
//验证签名，对收到的原始报文和秘钥进行md5加密
String localMac=Md5.MD5(context+JSONUtils.CHECK_STR);
		//读取通知的报文内容
		JSONObject requestData=JSONUtils.getRequestParamStream(context);
		JSONObject ret=null;
		if(localMac.equals(mac)){
            if("00".equals(requestData.getString("code"))){
                requestData.put("response_code", "00");
                requestData.put("response_msg", "交易成功");
                String transtype=requestData.getString("transtype");
				if("P033".equals(transtype)){//支付通知
                    try {
                        //支付通知，商户自行处理,所需要的参数在map中取，参数key在repeustP033BodyNodes里面
                        //此处更新数据库的动作省略....
                        //组装响应的报文
                        ret=JSONUtils.getResponseParam(requestData,JSONUtils.responseP033BodyNodes);
                    } catch (Exception e) {
                        //如果操作数据库异常
                        requestData.put("response_code", "01");
                        requestData.put("response_msg", "系统内部异常:"+e.getMessage());
                        ret=JSONUtils.getResponseParam(requestData,JSONUtils.responseErrorBodyNodes);
                    }
				}else if("P036".equals(transtype)){//退款通知
                    try {
                        //退款通知，商户自行处理,所需要的参数在map中取，参数key在repeustP036BodyNodes里面
                        //此处更新数据库的动作省略....
                        //组装响应的报文
                        ret=JSONUtils.getResponseParam(requestData,JSONUtils.responseP033BodyNodes);
                    } catch (Exception e) {
                        //如果操作数据库异常
                        requestData.put("response_code", "01");
                        requestData.put("response_msg", "系统内部异常:"+e.getMessage());
                        ret=JSONUtils.getResponseParam(requestData,JSONUtils.responseErrorBodyNodes);
                    }
				}else{
                    requestData.put("response_code", "01");
                    requestData.put("response_msg", "transtype错误");
                    ret=JSONUtils.getResponseParam(requestData,JSONUtils.responseErrorBodyNodes);
                }
			}else{
                requestData.put("response_code", "01");
                requestData.put("response_msg", requestData.get("msg"));
                ret=JSONUtils.getResponseParam(requestData,JSONUtils.responseErrorBodyNodes);
            }
        }else{
            requestData.put("response_code", "01");
            requestData.put("response_msg", "MAC签名不一致");
            ret=JSONUtils.getResponseParam(requestData,JSONUtils.responseErrorBodyNodes);
        }
		String responseMac=Md5.MD5(ret.toString()+JSONUtils.CHECK_STR);
		System.out.println("回写给大华的信息是："+ret.toString()+"&mac="+responseMac);
		response.setCharacterEncoding("UTF-8");
		response.getWriter().write(ret.toString()+"&mac="+responseMac);
		response.getWriter().close();
	}
}