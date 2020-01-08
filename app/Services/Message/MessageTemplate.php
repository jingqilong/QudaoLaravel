<?php


namespace App\Services\Message;


use App\Services\BaseService;

class MessageTemplate extends BaseService
{

    /**
     * 消息模板列表
     * @var array
     */
    public $template_array = [
        1 => //执行人
'尊敬的[receiver_name]：
您好！
    渠道PLUS OA的工作流程[process_full_name]中又有新的审核流程需要处理了。请您及时处理。谢谢！
(注：此邮件无须回复！)祝您

健康快乐！',
        2 => //监督人
'',
        3 => //发起人
'尊敬的[$receiver_name]：

您好！

您所提交的[$process_full_name]已经[$precess_result]。感谢您的参与！

(注：此邮件无须回复！)祝您

健康快乐！',
        4 => //代理人
'',
    ];

    /**
     * 模板需要装载的数据
     * @var array
     */
    public $data = [];

    /**
     * 负责人的类型
     * @var int
     */
    public $principal_type = 0;

    /**
     * MessageTemplate constructor.
     * @param array $data
     * @param int $principal_type
     */
    public function __construct(array $data,int $principal_type)
    {
        $this->data             = $data;
        $this->principal_type   = $principal_type;
    }


    /**
     * 获取消息模板
     * @return bool|mixed
     */
    protected function getTemplate(){
        $principal_type = $this->principal_type;
        if (!isset($this->template_array[$principal_type])){
            $this->setError('消息模板不存在！');
            return false;
        }
        return $this->template_array[$principal_type];
    }

    /**
     * 获取消息内容
     * @return string
     */
    public function getContent(){
        $data           = $this->data;
        $template       = $this->getTemplate();
        return str_replace(['[receiver_name]','[process_full_name]'],[$data['receiver_name'],$data['process_full_name']],$template);
    }
}