<?php
namespace App\Services\Score;


use App\Enums\ScoreEnum;
use App\Repositories\ScoreCategoryRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;

class CategoryService extends BaseService
{
    use HelpTrait;

    /**
     * 获取积分类别接口
     * @param $request
     * @return mixed
     */
    public function getScoreCateGoryList($request){
        if (!$list = ScoreCategoryRepository::getList(['id' => ['<>',0]],['*'],'id','asc')){
            $this->setError('获取失败！');
            return false;
        }
        $list  = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        foreach ($list['data'] as &$value){
            $value['status']        = ScoreEnum::getStatus($value['status']);
            $value['is_cashing']    = ScoreEnum::getCashing($value['is_cashing']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            