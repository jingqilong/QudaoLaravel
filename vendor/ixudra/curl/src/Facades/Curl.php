<?php namespace Ixudra\Curl\Facades;


use Illuminate\Support\Facades\Facade;

/**
 * @property array $curlOptions
 * @property array $packageOptions
 * @method static Curl to($url)
 * @method Curl withTimeout($timeout = 30.0)
 * @method Curl withData($data = array())
 * @method Curl withFile($key, $path, $mimeType = '', $postFileName = '')
 * @method Curl allowRedirect()
 * @method Curl asJson($asArray = false)
 * @method Curl asJsonRequest()
 * @method Curl asJsonResponse($asArray = false)
 * @method Curl withOption($key, $value)
 * @method Curl setCookieFile($cookieFile)
 * @method Curl setCookieJar($cookieJar)
 * @method Curl withCurlOption($key, $value)
 * @method Curl withPackageOption($key, $value)
 * @method Curl withHeader($header)
 * @method Curl withHeaders(array $headers)
 * @method Curl withContentType($contentType)
 * @method Curl withResponseHeaders()
 * @method Curl returnResponseObject()
 * @method Curl returnResponseArray()
 * @method Curl enableDebug($logFile)
 * @method Curl withProxy($proxy, $port = '', $type = '', $username = '', $password = '')
 * @method Curl containsFile()
 * @method Curl enableXDebug($sessionName = 'session_1')
 * @method get()
 * @method post()
 * @method Curl download($fileName)
 * @method Curl setPostParameters()
 * @method Curl getCurlFileValue($filename, $mimeType, $postFileName)
 * @method put()
 * @method patch()
 * @method delete()
 * @method send()
 * @method Curl parseHeaders($headerString)
 * @method Curl returnResponse($content, array $responseData = array(), $header = null)
 * @method Curl forgeOptions()
 * @method Curl appendDataToURL()
 * Class Curl
 * @package Ixudra\Curl\Facades
 */
class Curl extends Facade {

    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Curl';
    }

}