<?php


namespace App\Models\Abstracts;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator as BasePaginator;

class ApiPaginator extends BasePaginator
{
    /**
     * 将新增的分页方法注册到查询构建器中，以便在模型实例上使用
     * 注册方式：
     * 在 AppServiceProvider 的 boot 方法中注册：ApiPaginator::injectIntoBuilder();
     * 使用方式：
     * 将之前代码中在模型实例上调用 paginate 方法改为调用 apiPaginate 方法即可：
     * Article::where('status', 1)->apiPaginate(15, ['*'], 'page', page);
     */
    public static function injectIntoBuilder()
    {
        Builder::macro('apiPaginate', function ($perPage, $columns, $pageName, $page) {
            $perPage = $perPage ?: $this->model->getPerPage();

            $items = ($total = $this->toBase()->getCountForPagination())
                ? $this->forPage($page, $perPage)->get($columns)
                : $this->model->newCollection();

            $options = [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ];

            return Container::getInstance()->makeWith(ApiPaginator::class, compact(
                'items', 'total', 'perPage', 'page', 'options'
            ));
        });
    }

    /**
     * @desc  添加没有path links 的 toApiArray()
     * @return array
     */
    public function toApiArray(){
        return [
            'data' => $this->items->toArray(),
            'total' => $this->total,
            'current_page' => $this->currentPage,
            'last_page' => $this->lastPage,
            'per_page' => $this->perPage
        ];
    }
}