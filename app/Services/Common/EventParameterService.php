<?php


namespace App\Services\Common;


use App\Exceptions\ServiceException\OperatorDoesNotExistsException;
use App\Repositories\OaEmployeeRepository;

class EventParameterService
{
    protected $visitor_id = null;

    /**
     * EventParameterService constructor.
     * @param null $visitor_id
     */
    public function __construct($visitor_id)
    {
        $this->visitor_id = $visitor_id;
    }

    /**
     * @throws OperatorDoesNotExistsException
     */
    public function getOaParameter(){
        if (!$employee = OaEmployeeRepository::getOne(['id' => $this->visitor_id])){
            Throw new OperatorDoesNotExistsException($this->visitor_id.'该员工不存在！');
        }
        //TODO  此处做执行事件需要的参数获取
    }
}