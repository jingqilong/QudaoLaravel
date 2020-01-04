<?php


return [
    /**
     * 游客放行路由（目前仅为医疗模块）
     */
    'greenlight_routes'  =>[
        '/api/v1/medical/doctors_list' ,
        '/api/v1/medical/get_doctor' ,
        '/api/v1/medical/search_doctors_hospitals' ,
        '/api/v1/medical/get_departments_doctor' ,
        '/api/v1/medical/hospital_list' ,
        '/api/v1/medical/hospital_detail' ,
        '/api/v1/medical/get_departments_list'
    ],
    /**
     * 测试用户放行路由
     */
    'test_greenlight_routes' => [
        '/api/v1/activity/get_activity_detail',
        '/api/v1/activity/get_activity_detail_over',
        '/api/v1/activity/get_home_list',
        '/api/v1/common/common_list',
        '/api/v1/common/get_common_service_terms',
        '/api/v1/common/home',
        '/api/v1/enterprise/get_enterprise_info',
        '/api/v1/enterprise/get_enterprise_list',
        '/api/v1/house/all_facility_list',
        '/api/v1/house/get_home_list',
        '/api/v1/house/get_house_detail',
        '/api/v1/house/get_house_home_list',
        '/api/v1/loan/get_loan_info',
        '/api/v1/loan/get_loan_list',
        '/api/v1/medical/doctors_list',
        '/api/v1/medical/get_departments_doctor',
        '/api/v1/medical/get_departments_list',
        '/api/v1/medical/get_doctor',
        '/api/v1/medical/hospital_detail',
        '/api/v1/medical/hospital_list',
        '/api/v1/medical/search_doctors_hospitals',
        '/api/v1/member/address_list',
        '/api/v1/member/get_grade_card_list',
        '/api/v1/member/get_grade_service',
        '/api/v1/member/get_member_info',
        '/api/v1/member/get_member_list',
        '/api/v1/prime/get_home_list',
        '/api/v1/prime/get_merchant_detail',
        '/api/v1/project/get_project_list',
        '/api/v1/shop/category_list',
        '/api/v1/shop/get_goods_ad_details',
        '/api/v1/shop/get_goods_details',
        '/api/v1/shop/get_goods_list',
        '/api/v1/shop/get_goods_spec',
        '/api/v1/shop/get_home',
    ],
];