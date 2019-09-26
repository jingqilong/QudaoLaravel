<?php
namespace App\Api\Controllers;

use App\Exceptions\FieldDoesNotExistException;
use Dingo\Api\Http\Request;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ApiController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public $request;

    public $error;

    public $id;

    /**
     * ApiController constructor.
     */
    public function __construct()
    {
        $this->request = app('request')->toArray();
    }

    public function returnJson($message, $data, $code = 200){
        echo json_encode(['code' => $code, 'message' => $message, 'data' => $data]);exit;
    }

    /**
     * 返回Request中指定的列
     * @param $array
     * @return array
     * @throws FieldDoesNotExistException
     */
    public function onlyRequest(array $array){
        $result = [];
        foreach ($array as $v){
            if (!isset($this->request[$v])){
                throw new FieldDoesNotExistException('列不存在');
            }
            $result[$v] = $this->request[$v];
        }
        return $result;
    }

    /**
     * 提交数据验证
     * @param array $key 验证规则  $rules = ['name' => ['required']];
     * @param array $errMsg 错误代码  $errMsg = ['name.required'=>'字段不能为空'];
     * @return mixed
     */
    public function ApiValidate($key,$errMsg){
        $payload = app('request')->only(collect($key)->keys()->toArray());
        $validator = app('validator')->make($payload, $key,$errMsg);
        $error = $validator->errors()->toArray();
        if (!empty($error)){
            $error = reset($error);
            $this->error = reset($error);
        }
        return $validator;
    }
}