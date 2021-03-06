<?php
namespace App\Services\Common;


use App\Repositories\CommonFeedbackRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;

class FeedbackService extends BaseService
{
    public $auth;

    /**
     * CollectService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }
    /**
     * 添加用户反馈
     * @param $request
     * @return bool
     */
    public function addFeedBack($request)
    {
        $member     = $this->auth->user();
        $content    = $request['content'] ?? '';
        $mobile     = $request['mobile'] ?? '';
        $add_arr = [
            'member_id' => $member->id,
            'content'   => $content,
            'mobile'    => $mobile,
        ];
        if (CommonFeedbackRepository::exists($add_arr)){
            $this->setError('您的信息已提交!');
            return false;
        }
        if (!CommonFeedbackRepository::getAddId($add_arr)){
            $this->setError('信息提交失败!');
            return false;
        }
        $this->setMessage('感谢您的反馈!');
        return true;
    }
}
            