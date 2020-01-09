<?php
namespace App\Services\Common;


use App\Enums\MemberEnum;
use App\Repositories\CommonFeedBacksRepository;
use App\Repositories\CommonFeedBacksViewRepository;
use App\Repositories\MemberGradeRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class FeedBacksService extends BaseService
{
    use HelpTrait;
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
        $content    = $request['content'] ?? null;
        $mobile     = $request['mobile'] ?? null;
        $add_arr = [
            'member_id' => $member->id,
            'content'   => $content,
            'mobile'    => $mobile,
        ];
        if (CommonFeedBacksRepository::exists($add_arr)){
            $this->setError('您的信息已提交!');
            return false;
        }
        $add_arr['created_at'] = time();
        if (!CommonFeedBacksRepository::getAddId($add_arr)){
            $this->setError('信息提交失败!');
            return false;
        }
        $this->setMessage('感谢您的反馈!');
        return true;
    }

    /**
     * oa 获取成员反馈
     * @param $request
     * @return bool|mixed|null
     */
    public function feedBackList($request)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $keywords   = $request['keywords'] ?? null;
        $where      = ['id' => ['>',1]];
        if (!empty($keywords)){
            $keyword   = [$keywords => ['ch_name','mobile','content']];
            if (!$list = CommonFeedBacksViewRepository::search($keyword,$where,['*'],$page,$page_num,'id','desc')){
                $this->setError('获取失败!');
                return false;
            }
        }else{
            if (!$list = CommonFeedBacksViewRepository::getList($where,['*'],'id','desc',$page,$page_num)){
                $this->setError('获取失败!');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            return $list;
        }
        $list['data'] = MemberGradeRepository::bulkHasManyWalk(
            $list['data'],
            ['from' => 'member_id','to' => 'user_id'],
            ['user_id','grade'],
            [],
            function($src_item,$src_items){
                $grade = Arr::only($src_items[$src_item['member_id']],'grade');
                $src_item['grade'] = MemberEnum::getGrade((int)$grade,'普通成员');
                return $src_item;
            }
        );
        $this->setMessage('获取成功!');
        return $list;
    }
}