<?php


namespace App\Repositories;


use App\Enums\MemberEnum;
use App\Models\MemberInfoModel;
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

    }
}
            