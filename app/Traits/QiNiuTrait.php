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
    //测试
    public $module_config_test = [
        'Activity'      => [//之前的图片存储库
            'bucket' => 'test-activity-img',
            'domains'=> [
                'default'   => 'pxpguwpi0.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'pxpguwpi0.bkt.clouddn.com',
            ],
        ],
        'ActivityTemp'  => [//更改过后的图片存储库
            'bucket' => 'test-activity-img',
            'domains'=> [
                'default'   => 'pxpguwpi0.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'pxpguwpi0.bkt.clouddn.com',
            ],
        ],
        'Estate'        => [//房产
            'bucket' => 'test-estate-img',
            'domains'=> [
                'default'   => 'pxpgs3c6d.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'pxpgs3c6d.bkt.clouddn.com',
            ],
        ],
        'Goods'         => [//商品
            'bucket' => 'test-shop-main-img',
            'domains'=> [
                'default'   => 'pxpgvz2ko.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'pxpgvz2ko.bkt.clouddn.com',
            ],
        ],
        'Member'        => [//会员
            'bucket' => 'test-member-img',
            'domains'=> [
                'default'   => 'pxpgn5daq.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'pxpgn5daq.bkt.clouddn.com',
            ],
        ],
        'Project'       => [//精选生活
            'bucket' => 'test-project-img',
            'domains'=> [
                'default'   => 'pxpfz4iid.bkt.clouddn.com',
                'https'     => '',
                'custom'    => 'pxpfz4iid.bkt.clouddn.com',
            ],
        ],
    ];
    //正式图片迁移配置
    public $module_config = [
        'Activity'      => [//之前的图片存储库
            'bucket' => 'qudao-activity-img',
            'domains'=> [
                'default'   => 'activity.shop.qudaoplus.cn',
                'https'     => '',
                'custom'    => 'activity.shop.qudaoplus.cn',
            ],
        ],
        'ActivityTemp'  => [//更改过后的图片存储库
            'bucket' => 'qudao-activity-img',
            'domains'=> [
                'default'   => 'activity.shop.qudaoplus.cn',
                'https'     => '',
                'custom'    => 'activity.shop.qudaoplus.cn',
            ],
        ],
        'Estate'        => [//房产
            'bucket' => 'qudao-estate-img',
            'domains'=> [
                'default'   => 'estate.shop.qudaoplus.cn',
                'https'     => '',
                'custom'    => 'estate.shop.qudaoplus.cn',
            ],
        ],
        'Goods'         => [//商品
            'bucket' => 'qudao-shop-img',
            'domains'=> [
                'default'   => 'shop.shop.qudaoplus.cn',
                'https'     => '',
                'custom'    => 'shop.shop.qudaoplus.cn',
            ],
        ],
        'Member'        => [//会员
            'bucket' => 'qudao-member-img',
            'domains'=> [
                'default'   => 'member.shop.qudaoplus.cn',
                'https'     => '',
                'custom'    => 'member.shop.qudaoplus.cn',
            ],
        ],
        'Project'       => [//精选生活
            'bucket' => 'qudao-project-img',
            'domains'=> [
                'default'   => 'project.shop.qudaoplus.cn',
                'https'     => '',
                'custom'    => 'project.shop.qudaoplus.cn',
            ],
        ],
    ];

    //上传图片至七牛云配置
    public $upload_config = [
        'ACTIVITY'      => [//精彩活动
            'bucket' => 'qudao-activity-img',
            'domains'=> [
                'default'   => 'activity.shop.qudaoplus.cn',
                'https'     => '',
                'custom'    => 'activity.shop.qudaoplus.cn',
            ],
        ],
        'DOCTOR'  => [//医疗特约
            'bucket' => 'qudao-doctor-img',
            'domains'=> [
                'default'   => 'doctor.shop.qudaoplus.cn',
                'https'     => '',
                'custom'    => 'doctor.shop.qudaoplus.cn',
            ],
        ],
        'ENTERPRISE'  => [//企业咨询
            'bucket' => 'qudao-enterprise-img',
            'domains'=> [
                'default'   => 'enterprise.shop.qudaoplus.cn',
                'https'     => '',
                'custom'    => 'enterprise.shop.qudaoplus.cn',
            ],
        ],
        'ESTATE'        => [//房产-租赁
            'bucket' => 'qudao-estate-img',
            'domains'=> [
                'default'   => 'estate.shop.qudaoplus.cn',
                'https'     => '',
                'custom'    => 'estate.shop.qudaoplus.cn',
            ],
        ],
        'HEADING'        => [//会员头像
            'bucket' => 'qudao-heading',
            'domains'=> [
                'default'   => 'heading.shop.qudaoplus.cn',
                'https'     => '',
                'custom'    => 'heading.shop.qudaoplus.cn',
            ],
        ],
        'ITEMS'         => [//项目对接
            'bucket' => 'qudao-items-img',
            'domains'=> [
                'default'   => 'items.shop.qudaoplus.cn',
                'https'     => '',
                'custom'    => 'pxnwg69e5.bkt.clouddn.com',
            ],
        ],
        'MEMBER'        => [//成员风采
            'bucket' => 'qudao-member-img',
            'domains'=> [
                'default'   => 'member.shop.qudaoplus.cn',
                'https'     => '',
                'custom'    => 'member.shop.qudaoplus.cn',
            ],
        ],
        'PROJECT'       => [//精选生活
            'bucket' => 'qudao-project-img',
            'domains'=> [
                'default'   => 'project.shop.qudaoplus.cn',
                'https'     => '',
                'custom'    => 'project.shop.qudaoplus.cn',
            ],
        ],
        'SHOP'         => [//商城模块
            'bucket' => 'qudao-shop-img',
            'domains'=> [
                'default'   => 'shop.shop.qudaoplus.cn',
                'https'     => '',
                'custom'    => 'shop.shop.qudaoplus.cn',
            ],
        ],
        'SPACE'         => [//私享空间
            'bucket' => 'qudao-space-img',
            'domains'=> [
                'default'   => 'space.shop.qudaoplus.cn',
                'https'     => '',
                'custom'    => 'space.shop.qudaoplus.cn',
            ],
        ],
    ];
}