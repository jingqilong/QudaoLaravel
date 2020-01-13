<?php


namespace App\Repositories;


use App\Enums\MemberEnum;
use App\Models\MemberInfoModel;
use App\Repositories\MemberGradeDefineRepository;
use App\Repositories\Traits\RepositoryTrait;
use App\Services\Common\ImagesService;

class MemberInfoRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param MemberInfoModel $model
     */
    public function __construct(MemberInfoModel $model)
    {
        $this->model = $model;
    }

    /**
     * 首页显示是否的OA列表
     * @param $is_home_detail
     * @param $column
     * @param $page
     * @param $page_num
     * @param $order
     * @param $asc
     * @return bool|mixed|null
     */
    protected function getScreenMemberList($is_home_detail,$column, $page, $page_num, $order, $asc)
    {
        $info_column = ['member_id','is_recommend','is_home_detail','employer'];
        if (!$list = $this->getList(['is_home_detail' => $is_home_detail], $info_column, $order, $asc, $page, $page_num )) {
            return false;
        }
        $member_ids  = array_column($list['data'],'member_id');
        $member_base_list  = MemberBaseRepository::getList(['id' => ['in',$member_ids]],$column);
        $member_base_info  = ImagesService::getListImagesConcise($member_base_list,['avatar_id' => 'single']);
        if (empty($member_grade_list = MemberGradeRepository::getList(['user_id' => ['in',$member_ids]],['user_id','grade']))){
            $member_grade_list = ['user_id' => 0,'grade' => 0,];
        }
        $member_base_arr  = [];
        $member_grade_arr = [];
        foreach ($list['data'] as &$value){
            if ($member_base = $this->searchArray($member_base_info,'id',$value['member_id'])){
                $member_base_arr = reset($member_base);
            }
            if ($member_grade = $this->searchArray($member_grade_list,'user_id',$value['id'])){
                $member_grade_arr = reset($member_grade);
            }
            $value = array_merge($value,$member_base_arr,$member_grade_arr);
            if (empty($list['is_recommend'])) $value['is_recommend'] = '0'; else $value['is_recommend'] == 0 ? 0 : 1;
            //if (empty($value['grade'])) $value['grade_name'] = '普通成员'; else $value['grade_name'] = MemberEnum::getGrade($value['grade']) ;
            $value['grade_name'] = MemberGradeDefineRepository::getLabelById($value['grade'],'普通成员');
            $value['category_name'] = MemberEnum::getCategory($value['category'],'普通成员');
            $value['sex_name']      = MemberEnum::getSex($value['sex'],'未设置');
            $value['status_name']   = MemberEnum::getStatus($value['status'],'成员');
            $value['hidden_name']   = MemberEnum::getHidden($value['hidden'],'显示');
            $value['img_url']       = $value['avatar_url']; #前端适配字段名
            unset($value['member_id'],$value['user_id'],$value['avatar_url']);
        }
        return $list;
    }
}
            