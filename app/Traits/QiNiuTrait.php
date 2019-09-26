<?php


namespace App\Traits;


trait QiNiuTrait
{
    //模块与数据库表的对应
    public $correspond_arr = [
        'Activity'      => 'my_hd_images',      //之前的图片存储库
        'ActivityTemp'  => 'my_hd_activity',    //更改过后的图片存储库
        'Estate'        => 'my_fc_photo',      //房产
        'Goods'         => 'my_sc_goods_common',//商品
        'Member'        => 'my_merber',         //会员
        'Project'       => 'my_th_project',     //精选生活
    ];
    //每个数据表中要查询的字段
    public $columns = [
        'my_hd_images'      => ['i_id', 'i_image'],
        'my_hd_activity'    => ['a_id', 'a_img'],
        'my_fc_photo'       => ['p_id', 'p_photo'],
        'my_sc_goods_common' => ['goods_common_id', 'main_img', 'main2_img', 'main3_img', 'main4_img', 'main5_img', 'desc_img', 'adve_img'],
        'my_merber'         => ['m_id', 'm_img'],
        'my_th_project'     => ['p_id', 'p_introimg', 'p_image'],
    ];
    //每个表中要上传的字段 key是图片表对应的id，value是表中原图片路径
    public $img_field = [
        'my_hd_images'      => [
            'img_id' => 'i_image'
        ],
        'my_hd_activity'    => [
            'img_id' => 'a_img'
        ],
        'my_fc_photo'       => [
            'img_id' => 'p_photo'
        ],
        'my_sc_goods_common' => [
            'main_img_id' => 'main_img',
            'main2_img_id' => 'main2_img',
            'main3_img_id' => 'main3_img',
            'main4_img_id' => 'main4_img',
            'main5_img_id' => 'main5_img',
            'desc_img_id' => 'desc_img',
            'adve_img_id' => 'adve_img'
        ],
        'my_merber'         => [
            'm_img_id' => 'm_img'
        ],
        'my_th_project'     => [
            'introimg_id' => 'p_introimg',
            'image_id' => 'p_image'
        ],
    ];
//    //测试
//    public $module_config = [
//        'Activity'      => [//之前的图片存储库
//            'bucket' => 'test-activity-img',
//            'domains'=> [
//                'default'   => 'pxpguwpi0.bkt.clouddn.com',
//                'https'     => '',
//                'custom'    => 'pxpguwpi0.bkt.clouddn.com',
//            ],
//        ],
//        'ActivityTemp'  => [//更改过后的图片存储库
//            'bucket' => 'test-activity-img',
//            'domains'=> [
//                'default'   => 'pxpguwpi0.bkt.clouddn.com',
//                'https'     => '',
//                'custom'    => 'pxpguwpi0.bkt.clouddn.com',
//            ],
//        ],
//        'Estate'        => [//房产
//            'bucket' => 'test-estate-img',
//            'domains'=> [
//                'default'   => 'pxpgs3c6d.bkt.clouddn.com',
//                'https'     => '',
//                'custom'    => 'pxpgs3c6d.bkt.clouddn.com',
//            ],
//        ],
//        'Goods'         => [//商品
//            'bucket' => 'test-shop-main-img',
//            'domains'=> [
//                'default'   => 'pxpgvz2ko.bkt.clouddn.com',
//                'https'     => '',
//                'custom'    => 'pxpgvz2ko.bkt.clouddn.com',
//            ],
//        ],
//        'Member'        => [//会员
//            'bucket' => 'test-member-img',
//            'domains'=> [
//                'default'   => 'pxpgn5daq.bkt.clouddn.com',
//                'https'     => '',
//                'custom'    => 'pxpgn5daq.bkt.clouddn.com',
//            ],
//        ],
//        'Project'       => [//精选生活
//            'bucket' => 'test-project-img',
//            'domains'=> [
//                'default'   => 'pxpfz4iid.bkt.clouddn.com',
//                'https'     => '',
//                'custom'    => 'pxpfz4iid.bkt.clouddn.com',
//            ],
//        ],
//    ];
    //正式图片迁移配置
    public $module_config = [
        'Activity'      => [//之前的图片存储库
            'bucket' => 'qudao-activity-img',
            'domains'=> [
                'default'   => 'pxnvlqdh3.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'pxnvlqdh3.bkt.clouddn.com',
            ],
        ],
        'ActivityTemp'  => [//更改过后的图片存储库
            'bucket' => 'qudao-activity-img',
            'domains'=> [
                'default'   => 'pxnvlqdh3.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'pxnvlqdh3.bkt.clouddn.com',
            ],
        ],
        'Estate'        => [//房产
            'bucket' => 'qudao-estate-img',
            'domains'=> [
                'default'   => 'pxnwc1cfd.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'pxnwc1cfd.bkt.clouddn.com',
            ],
        ],
        'Goods'         => [//商品
            'bucket' => 'qudao-shop-img',
            'domains'=> [
                'default'   => 'py0hcd3dv.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'py0hcd3dv.bkt.clouddn.com',
            ],
        ],
        'Member'        => [//会员
            'bucket' => 'qudao-member-img',
            'domains'=> [
                'default'   => 'pxnv4f3zi.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'pxnv4f3zi.bkt.clouddn.com',
            ],
        ],
        'Project'       => [//精选生活
            'bucket' => 'qudao-project-img',
            'domains'=> [
                'default'   => 'pxnw51r2j.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'pxnw51r2j.bkt.clouddn.com',
            ],
        ],
    ];

    //上传图片至七牛云配置
    public $upload_config = [
        'ACTIVITY'      => [//精彩活动
            'bucket' => 'qudao-activity-img',
            'domains'=> [
                'default'   => 'pxnvlqdh3.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'pxnvlqdh3.bkt.clouddn.com',
            ],
        ],
        'DOCTOR'  => [//医疗特约
            'bucket' => 'qudao-doctor-img',
            'domains'=> [
                'default'   => 'pxnvdrmmr.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'pxnvdrmmr.bkt.clouddn.com',
            ],
        ],
        'ENTERPRISE'  => [//企业咨询
            'bucket' => 'qudao-enterprise-img',
            'domains'=> [
                'default'   => 'pxnw137no.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'pxnw137no.bkt.clouddn.com',
            ],
        ],
        'Estate'        => [//房产-租赁
            'bucket' => 'qudao-estate-img',
            'domains'=> [
                'default'   => 'pxnwc1cfd.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'pxnwc1cfd.bkt.clouddn.com',
            ],
        ],
        'HEADING'        => [//会员头像
            'bucket' => 'qudao-heading',
            'domains'=> [
                'default'   => 'pxntxvo6y.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'pxntxvo6y.bkt.clouddn.com',
            ],
        ],
        'ITEMS'         => [//项目对接
            'bucket' => 'qudao-items-img',
            'domains'=> [
                'default'   => 'pxnwg69e5.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'pxnwg69e5.bkt.clouddn.com',
            ],
        ],
        'MEMBER'        => [//成员风采
            'bucket' => 'qudao-member-img',
            'domains'=> [
                'default'   => 'pxnv4f3zi.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'pxnv4f3zi.bkt.clouddn.com',
            ],
        ],
        'PROJECT'       => [//精选生活
            'bucket' => 'qudao-project-img',
            'domains'=> [
                'default'   => 'pxnw51r2j.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'pxnw51r2j.bkt.clouddn.com',
            ],
        ],
        'SHOP'         => [//商城模块
            'bucket' => 'qudao-shop-img',
            'domains'=> [
                'default'   => 'py0hcd3dv.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'py0hcd3dv.bkt.clouddn.com',
            ],
        ],
        'SPACE'         => [//私享空间
            'bucket' => 'qudao-space-img',
            'domains'=> [
                'default'   => 'pxnwkrubu.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'pxnwkrubu.bkt.clouddn.com',
            ],
        ],
    ];
}