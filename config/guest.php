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
    ]
];