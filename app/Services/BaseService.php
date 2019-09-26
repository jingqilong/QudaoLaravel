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
     * 添加错误信息
     * @param $error
     */
    public function setError($error){
        $this->error = $error;
    }

    /**
     * 添加提示信息
     * @param $message
     */
    public function setMessage($message){
        $this->message = $message;
    }
}