<?php


namespace App\Repositories;


use App\Models\ActivityThemeModel;
use App\Repositories\Traits\RepositoryTrait;

/**
 * <这是为IDE添加自动完成的以及调试所用的注解>
 *
 * @method        int           WINEPARTY()                                                                               Get the Activety typr id.
 * @method        int           FORUM()                                                                                  Get the Activety typr id.
 * @method        int           SALOON()                                                                             Get the Activety typr id.
 * @method        int           GIFTS()                                                                               Get the Activety typr id.
 *
 * <end 自动完成>
 *
 */
class ActivityThemeRepository extends EnumerableRepository
{
    use RepositoryTrait;

    protected $columns_map = ['id'=>'id','label'=>'name','name'=>'tag'];

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ActivityThemeModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取所有主题列表
     * @param $where
     * @param $column
     * @return array|null
     */
    protected function getAllThemeList($where, $column){
        if (!$theme_list = $this->getAllList($where,$column)){
            return [];
        }
        CommonImagesRepository::bulkHasOneWalk(byRef($theme_list),['from' => 'icon_id','to' => 'id'],['id','img_url'],[],
            function ($src_item,$set_items){
                $src_item['theme_icon'] = $set_items['img_url'];
                return $src_item;
            });
        $theme_list = $this->createArrayIndex($theme_list,'id');
        return $theme_list;
    }
}
            