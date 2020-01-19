<?php
namespace App\Api\Controllers;

use App\Exceptions\FieldDoesNotExistException;
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

    /**
     * 如果不传$keys 则要传入含有所有键值的 $default
     * @param array|null $keys
     * @param array|null $default
     * @return array
     */
    public function input($keys = null, $default = null){
        $request = app('request');
        if(null === $keys){
            if(null == $default){
                return $request->except(['sign','token','page','page_num']);
            }else{
                $keys = array_keys($default);
                return $request->input($keys,$default);
            }
        }
        if(null == $default) {
            return $request->input($keys);
        }
        return $request->input($keys,$default);
    }
    
     /**
     * 获取当前传入的分页参数
     * 如果要分开到变量中：list($page,$page_num) = $this->inputPage();
     * @param int $per_page
     * @return array
     */
    public function inputPage($per_page = 10){
        return [
            request('page',1),
            request('page_num',$per_page)
        ];
    }


    /**
     * 批量设置默认值的函数
     * @param null $null_keys , 要设为NULL的键值
     * @param null $empty_string_keys ,要设为''的键值
     * @param null $zero_keys ,要设为0的键值
     * @return array
     */
    public function setDefault($null_keys = null,$empty_string_keys = null,$zero_keys = null){
        $param_array = [$null_keys,$empty_string_keys,$zero_keys];
        $default = [null,'',0];
        $return_array = [];
        for($i=0,$j=count($param_array);$i<$j;$i++){
            if(null !== $param_array[$i]){
                $new_array = array_combine($param_array[$i], array_fill(0, count($param_array[$i]), $default[$i]));
                $return_array = array_merge($return_array,$new_array);
            }
        }
        return $return_array;
    }
}