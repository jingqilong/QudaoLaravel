<?php
return [
    //消息数量缓存key
    'cache-key'  => 'message_count',
    //消息数量缓存通道
    'cache-chanel' => [
        1    => 'h5.',  //成员
        2    => 'me.',  //商户
        3    => 'oa.'   //OA员工
    ],

    //意见反馈缓存key
    'feed_back' => [
        'cache_key' => 'feed_back_key',
        //消息数量缓存通道
        'cache_chanel' => [
            0    => 'h5.',  //成员
            1    => 'oa.'   //OA员工
            ]
    ],

];