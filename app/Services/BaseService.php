<?php


namespace App\Services;


class BaseService
{
    /**
     * 错误信息
     * @var string
     */
    public $error;

    /**
     * 信息
     * @var string
     */
    public $message;

    /**
     * 状态码
     * @var string
     */
    public $code;

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        $instance = app(get_called_class());
        return $instance->$method(...$parameters);
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->$method(...$parameters);
    }

    /**
     * 设置错误信息
     * @param $error
     * @param int $code
     */
    public function setError($error,$code = 100){
        $this->error = $error;
        $this->code = $code;
    }

    /**
     * 设置提示信息
     * @param $message
     * @param int $code
     */
    public function setMessage($message,$code = 200){
        $this->message = $message;
        $this->code = $code;
    }

    /**
     * 设置状态码
     * @param $code
     */
    public function setCode($code){
        $this->code = $code;
    }
}