<?php


namespace App\Repositories;


use App\Models\MemberGradeDefineModel;
use App\Repositories\Traits\RepositoryTrait;

/**
 * <这是为IDE添加自动完成的以及调试所用的注解>
 *
 * @method        int           DEFAULT()                                                                               Get the Member id.
 * @method        int           TEST()                                                                                  Get the Member id.
 * @method        int           ALSOENJOY()                                                                             Get the Member id.
 * @method        int           TOENJOY()                                                                               Get the Member id.
 * @method        int           YUEENJOY()                                                                              Get the Member id.
 * @method        int           REALLYENJOY()                                                                           Get the Member id.
 * @method        int           YOUENJOY()                                                                              Get the Member id.
 * @method        int           HONOURENJOY()                                                                           Get the Member id.
 * @method        int           ZHIRENJOY()                                                                             Get the Member id.
 * @method        int           ADVISER()                                                                               Get the Member id.
 * @method        int           TEMPORARY()                                                                             Get the Member id.
 *
 * <end 自动完成>
 *
 */
class MemberGradeDefineRepository extends EnumerableRepository
{

    use RepositoryTrait;

    protected $columns_map = ['id'=>'iden','label'=>'title','name'=>'tag'];

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberGradeDefineModel $model)
    {
        $this->model = $model;
    }
}
            