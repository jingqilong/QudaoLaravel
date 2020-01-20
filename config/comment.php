<?php
return [
    'relate_info' => [
        1 => [\App\Services\Shop\OrderGoodsService::class,'getCommentCommonInfo'],
        2 => [\App\Services\Activity\CommentsService::class,'getCommentCommonInfo']
    ]
];