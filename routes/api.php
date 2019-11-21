<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
$api = app('Dingo\Api\Routing\Router');



$api->version('v1',function ($api){
//    #预检请求处理、请勿删除
//    $api->options('/{all}', function(Request $request) {
//        $origin = $request->header('ORIGIN', '*');
//        header("Access-Control-Allow-Origin: $origin");
//        header("Access-Control-Allow-Credentials: true");
//        header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
//        header('Access-Control-Allow-Headers: Origin, Access-Control-Request-Headers, SERVER_NAME, Access-Control-Allow-Headers, cache-control, token, X-Requested-With, Content-Type, Accept, Connection, User-Agent, Cookie');
//    })->where(['all' => '([a-zA-Z0-9-]|/)+']);

    $api->get('swagger/doc','App\Api\Controllers\SwaggerController@doc');
    //不需要验签的接口
    $api->group(['prefix' => 'v1','middleware' => 'cors','namespace' => 'App\Api\Controllers\V1'], function ($api) {
        //测试
        $api->group(['prefix' => 'test'],function ($api){
            $api->get('test','TestApiController@index')->name('测试');
            $api->get('create_model','TestApiController@createModel')->name('批量创建模型');
            $api->get('create_repository','TestApiController@createRepository')->name('批量创建Repository');
            $api->get('create_service','TestApiController@createService')->name('批量创建Service');
        });
        $api->any('oa/push','Oa\MessageController@push')->name("添加web推送授权信息");

        //支付模块模块
        $api->group(['prefix' => 'payments', 'namespace' => 'Pay'], function ($api){
            $api->any('we_chat_pay_call_back', 'WeChatPayController@weChatPayCallBack')->name('微信小程序微信支付回调接口');
        });
    });
    //需要验签的接口
    $api->group(['prefix' => 'v1','middleware' => ['cors', 'sign'],'namespace' => 'App\Api\Controllers\V1'], function ($api) {
        //OA 模块
        $api->group(['prefix' => 'oa','namespace' => 'Oa'],function ($api){
            $api->group(['middleware' => ['oa.jwt.auth','oa.perm']],function($api){
                $api->post('logout','OaController@logout')->name('退出');
                $api->post('refresh','OaController@refresh')->name('刷新token');
                $api->get('get_user_info','OaController@getUserInfo')->name('获取用户信息');
                #OA部门
                $api->get('get_depart','DepartController@getDepart')->name("获取部门");
                $api->post('add_depart','DepartController@addDepart')->name("添加部门");
                $api->post('update_depart','DepartController@updateDepart')->name("修改部门");
                $api->delete('del_depart','DepartController@delDepart')->name("删除部门");
                $api->get('get_depart_list','DepartController@getDepartList')->name("获取部门列表");
                #OA员工
                $api->get('get_employee_list','EmployessController@getEmployeeList')->name('获取OA员工列表');
                $api->get('get_employee_info','EmployessController@getEmployeeInfo')->name('获取OA员工信息');
                $api->post('add_employee','EmployessController@addEmployee')->name('添加OA员工信息');
                $api->delete('del_employee','EmployessController@delEmployee')->name("删除OA员工");
                $api->post('update_employee','EmployessController@updateEmployee')->name("更新OA员工信息");
                $api->post('add_push_auth','MessageController@addPushAuth')->name("添加web推送授权信息");
                #OA权限管理
                $api->post('add_menu','PermissionsController@addMenu')->name("添加菜单");
                $api->post('edit_menu','PermissionsController@editMenu')->name("修改菜单");
                $api->get('menu_detail','PermissionsController@menuDetail')->name("菜单详情");
                $api->post('add_permission','PermissionsController@addPermission')->name("添加权限");
                $api->post('add_roles','PermissionsController@addRoles')->name("添加角色");
                $api->post('add_user','PermissionsController@addUser')->name("添加用户");
                $api->get('menu_list','PermissionsController@menuList')->name("获取菜单列表");
                $api->get('user_list','PermissionsController@userList')->name("获取用户列表");
                $api->post('is_disabled','PermissionsController@isDisabled')->name("禁用或开启员工");
                $api->get('permission_list','PermissionsController@permissionList')->name("获取权限列表");
                $api->get('role_list','PermissionsController@roleList')->name("获取角色列表");
                $api->get('operation_log','PermissionsController@operationLog')->name("获取操作日志");
                $api->get('menu_linkage_list','PermissionsController@menuLinkageList')->name("添加菜单使用父级菜单联动列表");

                #OA成员管理
                $api->get('member_list','OaMemberController@memberList')->name('获取成员列表');
                $api->get('get_member_info','OaMemberController@getMemberInfo')->name('获取成员信息');
                $api->delete('del_member','OaMemberController@delMember')->name('删除成员');
                $api->get('set_member_status','OaMemberController@setMemberStatus')->name('禁用or激活成员');
                $api->post('add_member','OaMemberController@addMember')->name('添加成员');
                $api->post('upd_member','OaMemberController@updMember')->name('修改完善成员');
                $api->get('get_all_menu_list','PermissionsController@getAllMenuList')->name("获取所有菜单列表，用于前端访问api");

                #OA流程
                $api->group(['prefix' => 'process'],function ($api){
                    $api->post('add_process_categories','ProcessController@addProcessCategories')->name('添加流程分类');
                    $api->delete('delete_process_categories','ProcessController@deleteProcessCategories')->name('删除流程分类');
                    $api->post('edit_process_categories','ProcessController@editProcessCategories')->name('修改流程分类');
                    $api->get('get_categories_list','ProcessController@getCategoriesList')->name('获取流程分类列表');

                    $api->post('create_process','ProcessController@createProcess')->name('创建流程');
                    $api->delete('delete_process','ProcessController@deleteProcess')->name('删除流程');
                    $api->post('edit_process','ProcessController@editProcess')->name('修改流程');
                    $api->get('get_process_list','ProcessController@getProcessList')->name('获取流程列表');
                    $api->post('get_process_detail','ProcessController@getProcessDetail')->name('获取流程详情');

                    $api->post('process_add_node','ProcessController@processAddNode')->name('流程添加节点');
                    $api->delete('delete_node','ProcessController@deleteNode')->name('删除流程节点');
                    $api->post('process_edit_node','ProcessController@processEditNode')->name('流程修改节点');

                    $api->post('add_event','ProcessController@addEvent')->name('添加事件');
                    $api->delete('delete_event','ProcessController@deleteEvent')->name('删除事件');
                    $api->post('edit_event','ProcessController@editEvent')->name('修改事件');
                    $api->get('get_event_list','ProcessController@getEventList')->name('获取事件列表');

                    $api->post('add_action','ProcessController@addAction')->name('添加动作');
                    $api->delete('delete_action','ProcessController@deleteAction')->name('删除动作');
                    $api->post('edit_action','ProcessController@editAction')->name('修改动作');
                    $api->get('get_action_list','ProcessController@getActionList')->name('获取动作列表');

                    $api->post('node_add_action','ProcessController@nodeAddAction')->name('给流程节点添加动作');
                    $api->delete('node_delete_action','ProcessController@nodeDeleteAction')->name('流程节点删除动作');
                    $api->post('action_add_related','ProcessController@actionAddRelated')->name('给流程节点动作事件与下一节点');
                    $api->post('action_add_principal','ProcessController@actionAddPrincipal')->name('流程节点动作添加负责人');

                    $api->post('process_record','ProcessController@processRecord')->name('记录流程进度');
                });
            });
            $api->post('login','OaController@login')->name('登录');
        });

        //精选生活
        $api->group(['prefix' => 'prime','namespace' => 'Prime'],function ($api){
            #精选生活商户后台
            $api->group(['prefix' => 'admin','middleware' => 'prime.jwt.auth'],function($api){
                $api->post('logout','AdminPrimeController@logout')->name('退出');
                $api->post('refresh','AdminPrimeController@refresh')->name('刷新token');
                $api->get('get_user_info','AdminPrimeController@getUserInfo')->name('获取用户信息');
                $api->post('edit_merchant','AdminPrimeController@editMerchant')->name('修改个人信息');

                $api->post('add_product','ProductPrimeController@addProduct')->name('添加产品');
                $api->delete('delete_product','ProductPrimeController@deleteProduct')->name('删除产品');
                $api->post('edit_product','ProductPrimeController@editProduct')->name('修改产品');
                $api->get('product_list','ProductPrimeController@productList')->name('获取产品列表');

                $api->get('get_reservation_list','ReservationController@getReservationList')->name('获取预约列表');
                $api->post('audit_reservation','ReservationController@auditReservation')->name('审核预约');

                $api->post('bill_settlement','ReservationController@billSettlement')->name('账单结算');
            });
            #精选生活OA后台
            $api->group(['middleware' => ['oa.jwt.auth','oa.perm']],function($api){
                $api->post('add_merchant','OaPrimeController@addMerchant')->name('添加商户');
                $api->post('disabled_merchant','OaPrimeController@disabledMerchant')->name('禁用或启用商户');
                $api->post('edit_merchant','OaPrimeController@editMerchant')->name('修改商户');
                $api->get('merchant_list','OaPrimeController@merchantList')->name('获取商户列表');
                $api->post('is_recommend','OaPrimeController@isRecommend')->name('推荐或取消推荐商户');
                $api->post('add_product','OaProductPrimeController@addProduct')->name('添加产品');
                $api->delete('delete_product','OaProductPrimeController@deleteProduct')->name('删除产品');
                $api->post('edit_product','OaProductPrimeController@editProduct')->name('修改产品');
                $api->get('product_list','OaProductPrimeController@productList')->name('获取产品列表');

                $api->post('audit','ReservationController@audit')->name('审核预约');
                $api->get('reservation_list','ReservationController@reservationList')->name('获取预约列表');
            });
            #精选生活
            $api->group(['middleware' => 'member.jwt.auth'],function($api){
                $api->post('reservation','ReservationController@reservation')->name('预约');
                $api->get('my_reservation_list','ReservationController@myReservationList')->name('获取我的预约列表');
                $api->get('my_reservation_detail','ReservationController@myReservationDetail')->name('获取我的预约详情');
                $api->post('edit_my_reservation','ReservationController@editMyReservation')->name('修改我的预约');
                $api->post('cancel_my_reservation','ReservationController@cancelMyReservation')->name('取消我的预约');

                $api->get('get_home_list','PrimeController@getHomeList')->name('获取首页列表');
                $api->get('get_merchant_detail','PrimeController@getMerchantDetail')->name('获取商户详情');
            });
            $api->post('admin/login','AdminPrimeController@login')->name('登录');
        });

        //精选活动模块（后台）
        $api->group(['prefix' => 'activity','namespace' => 'Activity'],function ($api){
            $api->group(['middleware' => ['oa.jwt.auth','oa.perm']],function($api){
                $api->post('add_activity','ActivityController@addActivity')->name('添加活动');
                $api->delete('delete_activity','ActivityController@deleteActivity')->name('软删除活动');
                $api->post('edit_activity','ActivityController@editActivity')->name('修改活动');
                $api->get('get_activity_list','ActivityController@getActivityList')->name('获取活动列表');
                $api->get('activity_detail','ActivityController@activityDetail')->name('获取获取详细信息');

                $api->post('activity_add_host','ActivityController@activityAddHost')->name('添加活动举办方');
                $api->delete('delete_host','ActivityController@deleteHost')->name('删除活动举办方');
                $api->post('edit_host','ActivityController@editHost')->name('修改活动举办方');

                $api->post('activity_add_link','ActivityController@activityAddLink')->name('添加活动相关链接');
                $api->delete('delete_link','ActivityController@deleteLink')->name('删除活动链接');
                $api->post('edit_link','ActivityController@editLink')->name('修改活动链接');

                $api->post('add_activity_theme','ThemeController@addActivityTheme')->name('添加活动主题');
                $api->delete('delete_activity_theme','ThemeController@deleteActivityTheme')->name('删除活动主题');
                $api->post('edit_activity_theme','ThemeController@editActivityTheme')->name('修改活动主题');
                $api->get('activity_theme_list','ThemeController@activityThemeList')->name('获取活动主题列表');

                $api->post('add_activity_site','SiteController@addActivitySite')->name('添加活动场地');
                $api->delete('delete_activity_site','SiteController@deleteActivitySite')->name('删除活动场地');
                $api->post('edit_activity_site','SiteController@editActivitySite')->name('修改活动场地');
                $api->get('activity_site_list','SiteController@activitySiteList')->name('获取活动场地列表');

                $api->post('add_activity_supplies','SuppliesController@addActivitySupplies')->name('添加活动用品');
                $api->delete('delete_activity_supplies','SuppliesController@deleteActivitySupplies')->name('删除活动用品');
                $api->post('edit_activity_supplies','SuppliesController@editActivitySupplies')->name('修改活动用品');
                $api->get('activity_supplies_list','SuppliesController@activitySuppliesList')->name('获取活动用品列表');

                $api->post('activity_add_prize','PrizeController@activityAddPrize')->name('活动添加奖品');
                $api->delete('activity_delete_prize','PrizeController@activityDeletePrize')->name('删除活动奖品');
                $api->post('activity_edit_prize','PrizeController@activityEditPrize')->name('修改奖品信息');
                $api->get('get_prize_list','PrizeController@getPrizeList')->name('获取活动奖品列表');
                $api->get('get_winning_list','PrizeController@getWinningList')->name('获取中奖列表');

                $api->get('get_register_list','RegisterController@getRegisterList')->name('获取活动报名列表');
                $api->get('get_sign_list','RegisterController@getSignList')->name('获取活动签到列表');
                $api->post('audit_register','RegisterController@auditRegister')->name('审核活动报名');

                $api->get('get_comment_list','CommentController@getCommentList')->name('获取活动评论列表');
                $api->post('audit_comment','CommentController@auditComment')->name('审核活动评论');
            });
        });

        $api->group(['prefix' => 'activity','namespace' => 'Activity'],function ($api){
            //精选活动（前台）
            $api->group(['middleware' => 'member.jwt.auth'],function($api){
                $api->post('activity_raffle','UserActivityController@activityRaffle')->name('成员活动抽奖');
                $api->post('is_collect_activity','UserActivityController@collectActivity')->name('收藏或取消收藏活动');
                $api->get('collect_list','UserActivityController@collectList')->name('获取活动收藏列表');
                $api->post('get_home_list','UserActivityController@getHomeList')->name('获取活动首页列表');
                $api->get('get_activity_detail','UserActivityController@activityDetail')->name('获取活动详情');

                $api->post('comment','CommentController@comment')->name('成员评论活动');
                $api->delete('delete_comment','CommentController@deleteComment')->name('成员删除评论');
                $api->get('get_activity_comment','CommentController@getActivityComment')->name('获取活动评论列表');

                $api->post('activity_register','RegisterController@activityRegister')->name('活动报名');
                $api->post('sign_in','RegisterController@signIn')->name('活动签到');
                $api->get('sign_in_list','RegisterController@signList')->name('获取活动签到列表');
            });
        });

        $api->group(['prefix' => 'message','namespace' => 'Message'],function ($api){
            //消息会员
            $api->group(['middleware' => 'member.jwt.auth'],function($api){
                $api->get('member_message_list','MessageController@memberMessageList')->name('会员消息列表');
                $api->get('member_message_details','MessageController@memberMessageDetails')->name('会员消息详情');
            });
            //消息后台
            $api->group(['middleware' => ['oa.jwt.auth','oa.perm']],function($api){
                $api->post('add_category','MessageCategoryController@addCategory')->name('添加消息分类');
                $api->post('disable_category','MessageCategoryController@disableCategory')->name('禁用或开启消息分类');
                $api->post('edit_category','MessageCategoryController@editCategory')->name('编辑消息分类');
                $api->get('get_category_list','MessageCategoryController@getCategoryList')->name('获取消息分类列表');

                $api->get('get_all_message_list','OaMessageController@getAllMessageList')->name('获取所有消息列表');
                $api->post('send_system_notice','OaMessageController@sendSystemNotice')->name('发送系统通知');
                $api->post('send_announce','OaMessageController@sendAnnounce')->name('发送公告');

                $api->get('oa_message_list','MessageController@oaMessageList')->name('OA员工消息列表');
                $api->get('oa_message_details','MessageController@oaMessageDetails')->name('OA员工消息详情');
            });
            //消息商户
            $api->group(['middleware' => 'prime.jwt.auth'],function($api){
                $api->get('merchant_message_list','MessageController@merchantMessageList')->name('商户消息列表');
                $api->get('merchant_message_details','MessageController@merchantMessageDetails')->name('商户消息详情');
            });
        });

        //成员模块
        $api->group(['prefix' => 'member','namespace' => 'Member'],function ($api){
            $api->group(['middleware' => 'member.jwt.auth'],function($api){
                $api->post('logout','MemberController@logout')->name('退出');
                $api->post('refresh','MemberController@refresh')->name('刷新token');
                $api->get('get_user_info','MemberController@getUserInfo')->name('获取成员信息');
                $api->get('get_member_list','MemberController@getMemberList')->name('获取成员列表');
                $api->get('get_member_info','MemberController@getMemberInfo')->name('获取成员信息');
                $api->post('edit_member_info','MemberController@editMemberInfo')->name('成员编辑个人信息');
                $api->get('get_member_category_list','MemberController@getMemberCategoryList')->name('根据查找分类获取成员列表');
                $api->get('update_user_info','MemberController@updateUserInfo')->name('更改用户信息');
                $api->any('update_user_password','MemberController@updateUserPassword')->name('更改用户密码');
                $api->any('sms_update_user_password','MemberController@forgetPassword')->name('短信验证码修改密码');
                $api->post('update_user_password','MemberController@updateUserPassword')->name('更改用户密码');
                $api->get('get_relation_list','MemberController@getRelationList')->name('获取用户推荐关系');
                $api->get('promote_qr_code','PublicController@promoteQrCode')->name('获取推广二维码');
                #用户地址管理
                $api->post('add_address','AddressController@addAddress')->name('用户添加地址');
                $api->delete('del_address','AddressController@delAddress')->name('用户删除地址');
                $api->post('edit_address','AddressController@editAddress')->name('用户编辑修改地址');
                $api->get('address_list','AddressController@addressList')->name('用户获取地址');
            });
            $api->group(['middleware' => ['oa.jwt.auth','oa.perm']],function($api){
                #成员权限（后台）
                $api->post('add_service','ServiceController@addService')->name('添加服务');
                $api->get('service_detail','ServiceController@serviceDetail')->name('获取服务详情');
                $api->post('edit_service','ServiceController@editService')->name('修改服务');
                $api->delete('delete_service','ServiceController@deleteService')->name('删除服务');
                $api->get('service_list','ServiceController@serviceList')->name('获取服务列表');
                $api->post('grade_add_service','ServiceController@gradeAddService')->name('给等级添加服务');
                $api->delete('grade_delete_service','ServiceController@gradeDeleteService')->name('删除等级中的服务');
                $api->post('grade_edit_service','ServiceController@gradeEditService')->name('修改等级与服务对应关系');
                $api->get('grade_service_detail','ServiceController@gradeServiceDetail')->name('获取等级下的服务详情');
                $api->post('add_view_member','ServiceController@addViewMember')->name('添加成员可查看成员');
                $api->post('add_grade_view','ServiceController@addGradeView')->name('添加等级可查看成员');
                $api->delete('delete_view_member','ServiceController@deleteViewMember')->name('软删除成员可查看成员');
                $api->post('restore_view_member','ServiceController@restoreViewMember')->name('恢复成员可查看成员');
                #OA用户地址管理
                $api->get('list_address','AddressController@listAddress')->name('OA用户地址管理');
            });
            $api->post('mobile_register','MemberController@mobileRegister')->name('手机号码注册登录');
            $api->post('perfect_member_info','MemberController@perfectMemberInfo')->name('手机号码注册完善用户信息');
            $api->post('login','MemberController@login')->name('登录');
            $api->post('sms_login','MemberController@smsLogin')->name('短信验证登录');
            $api->post('mini_login','Member\WeChatController@miniLogin')->name('微信小程序登录');
            $api->post('mini_bind_mobile','Member\WeChatController@miniBindMobile')->name('微信小程序绑定手机号');
            $api->post('mini_login','WeChatController@miniLogin')->name('微信小程序登录');
            $api->post('mini_bind_mobile','WeChatController@miniBindMobile')->name('微信小程序绑定手机号');
            $api->post('official_account_login','WeChatController@officialAccountLogin')->name('微信公众号登录');
            $api->post('official_account_bind_mobile','WeChatController@officialAccountBindMobile')->name('微信公众号登录绑定手机号');

            $api->post('we_chat_login','WeChatController@weChatLogin')->name('微信登录');
            $api->post('we_chat_bind_mobile','WeChatController@weChatBindMobile')->name('微信登录绑定手机号');

        });

        //医疗模块
        $api->group(['prefix' => 'medical', 'namespace' => 'Medical'], function ($api) {
            $api->group(['middleware' => 'member.jwt.auth'], function ($api) {
                $api->post('add_doctor_order', 'DoctorOrderController@addDoctorOrder')->name('添加医疗预约');
                $api->get('doctors_order_list', 'DoctorOrderController@doctorsOrderList')->name('获取成员自己预约列表');
                $api->get('doctors_order', 'DoctorOrderController@doctorsOrder')->name('根据id获取成员自己预约详情');
                $api->get('doctors_list', 'DoctorOrderController@doctorsList')->name('获取医生列表');
                $api->get('get_doctor', 'DoctorsController@getDoctor')->name('获取医生详情');
                $api->get('hospital_list', 'HospitalsController@hospitalList')->name('获取医疗医院列表');
                $api->get('hospital_detail', 'HospitalsController@hospitalDetail')->name('获取医疗医院详情');
            });
            #获取医疗订单列表
            $api->group(['middleware' => ['oa.jwt.auth','oa.perm']], function ($api) {
                $api->get('doctor_order_list', 'DoctorOrderController@doctorOrderList')->name('获取医疗预约列表');
                $api->post('set_doctor_order', 'DoctorOrderController@setDoctorOrder')->name('审核预约列表状态');
            });
            #添加医院
            $api->group(['middleware' => ['oa.jwt.auth','oa.perm']], function ($api) {
                $api->post('add_hospitals', 'HospitalsController@addHospitals')->name('添加医疗医院');
                $api->delete('delete_hospitals', 'HospitalsController@deleteHospitals')->name('删除医疗医院');
                $api->post('edit_hospitals', 'HospitalsController@editHospitals')->name('修改医疗医院');
                $api->get('hospitals_list', 'HospitalsController@hospitalsList')->name('获取医疗医院列表');
            });
            #添加医生
            $api->group(['middleware' => ['oa.jwt.auth','oa.perm']], function ($api) {
                $api->post('add_doctors', 'DoctorsController@addDoctors')->name('添加医生');
                $api->delete('delete_doctors', 'DoctorsController@deleteDoctors')->name('删除医生');
                $api->post('edit_doctors', 'DoctorsController@editDoctors')->name('修改医生信息');
                $api->get('doctors_list_page', 'DoctorsController@doctorsListPage')->name('获取医生列表');
            });
            #添加科室
            $api->group(['middleware' => ['oa.jwt.auth','oa.perm']], function ($api) {
                $api->post('add_departments', 'DepartmentsController@addDepartments')->name('添加医疗科室');
                $api->delete('delete_departments', 'DepartmentsController@deleteDepartments')->name('删除医疗科室');
                $api->post('edit_departments', 'DepartmentsController@editDepartments')->name('修改医疗科室');
                $api->get('departments_list', 'DepartmentsController@departmentsList')->name('获取医疗科室列表');
            });
            #添加医生标签
            $api->group(['middleware' => ['oa.jwt.auth','oa.perm']], function ($api) {
                $api->post('add_doctorLabels', 'DoctorLabelsController@addDoctorLabels')->name('添加医生标签');
                $api->delete('delete_doctorLabels', 'DoctorLabelsController@deleteDoctorLabels')->name('删除医生标签');
                $api->post('edit_doctorLabels', 'DoctorLabelsController@editDoctorLabels')->name('修改医生标签');
                $api->get('doctorLabels_list', 'DoctorLabelsController@doctorLabelsList')->name('获取医生标签列表');
            });
        });


        //房产模块
        $api->group(['prefix' => 'house', 'namespace' => 'House'], function ($api){
            $api->group(['middleware' => 'member.jwt.auth'],function($api) {
                #房源发布
                $api->post('publish_house', 'HouseController@publishHouse')->name('个人发布房源');
                $api->get('get_house_detail', 'HouseController@getHouseDetail')->name('获取房产详情');
                $api->delete('delete_self_house', 'HouseController@deleteSelfHouse')->name('个人删除房源');
                $api->get('get_home_list', 'HouseController@getHomeList')->name('获取房产首页列表');
                #预约
                $api->post('reservation', 'ReservationController@reservation')->name('预约看房');
                $api->get('reservation_list', 'ReservationController@reservationList')->name('个人预约列表');
                $api->get('is_reservation_list', 'ReservationController@isReservationList')->name('个人被预约列表');
            });
            #房产租赁后台
            $api->group(['middleware' => ['oa.jwt.auth','oa.perm']],function($api) {
                #房产详情
                $api->post('add_house', 'OaHouseController@addHouse')->name('添加房源');
                $api->delete('delete_house', 'OaHouseController@deleteHouse')->name('删除房源');
                $api->post('edit_house', 'OaHouseController@editHouse')->name('修改房源');
                $api->get('house_list', 'OaHouseController@houseList')->name('获取房产列表');
                $api->post('audit_house', 'OaHouseController@auditHouse')->name('审核房源');
                #房产设施
                $api->post('add_facility', 'FacilityController@addFacility')->name('添加房产设施');
                $api->delete('delete_facility', 'FacilityController@deleteFacility')->name('删除房产设施');
                $api->post('edit_facility', 'FacilityController@editFacility')->name('修改房产设施');
                $api->get('facility_list', 'FacilityController@facilityList')->name('获取房产设施列表');

                #房产朝向
                $api->post('add_toward', 'TowardController@addToward')->name('添加房产朝向');
                $api->delete('delete_toward', 'TowardController@deleteToward')->name('删除房产朝向');
                $api->post('edit_toward', 'TowardController@editToward')->name('修改房产朝向');
                $api->get('toward_list', 'TowardController@towardList')->name('获取房产朝向列表');

                #房产户型
                $api->post('add_unit', 'UnitController@addUnit')->name('添加房产户型');
                $api->delete('delete_unit', 'UnitController@deleteUnit')->name('删除房产户型');
                $api->post('edit_unit', 'UnitController@editUnit')->name('修改房产户型');
                $api->get('unit_list', 'UnitController@unitList')->name('获取房产户型列表');

                #房产租赁方式
                $api->post('add_lease', 'LeaseController@addLease')->name('添加房产租赁方式');
                $api->delete('delete_lease', 'LeaseController@deleteLease')->name('删除房产租赁方式');
                $api->post('edit_lease', 'LeaseController@editLease')->name('修改房产租赁方式');
                $api->get('lease_list', 'LeaseController@leaseList')->name('获取房产租赁方式列表');
                #预约
                $api->get('all_reservation_list', 'ReservationController@allReservationList')->name('预约列表');
                $api->post('audit_reservation', 'ReservationController@auditReservation')->name('审核预约');
            });
        });

        //贷款模块
        $api->group(['prefix' => 'loan', 'namespace' => 'Loan'], function ($api){
            $api->group(['middleware' => ['oa.jwt.auth','oa.perm']],function($api) {
                $api->get('get_loan_order_info', 'LoanController@getLoanOrderInfo')->name('根据ID查找贷款订单信息');
                $api->post('upd_loan', 'LoanController@updLoan')->name('修改贷款订单');
                $api->post('audit_loan', 'LoanController@auditLoan')->name('审核贷款订单');
                $api->delete('del_loan', 'LoanController@delLoan')->name('删除贷款订单');
                $api->get('get_loan_order_list', 'LoanController@getLoanOrderList')->name('获取所有贷款订单列表');
            });
            $api->group(['middleware' => 'member.jwt.auth'],function($api) {
                $api->post('add_loan', 'LoanController@addLoan')->name('添加贷款订单');
                $api->get('get_loan_info', 'LoanController@getLoanInfo')->name('获取贷款订单信息');
                $api->get('get_loan_list', 'LoanController@getLoanList')->name('获取成员本人贷款订单列表');
            });

        });

        //企业咨询模块
        $api->group(['prefix' => 'enterprise', 'namespace' => 'Enterprise'], function ($api){
            $api->group(['middleware' => 'member.jwt.auth'],function($api) {
                $api->post('add_enterprise', 'EnterpriseController@addEnterprise')->name('根据ID添加企业咨询订单');
                $api->post('edit_enterprise', 'EnterpriseController@editEnterprise')->name('根据ID修改企业咨询订单');
                $api->get('get_enterprise_list', 'EnterpriseController@getEnterpriseList')->name('获取本人企业咨询订单列表');
                $api->get('get_enterprise_info', 'EnterpriseController@getEnterpriseInfo')->name('根据ID获取企业咨询订单信息');
            });
            $api->group(['middleware' => ['oa.jwt.auth','oa.perm']],function($api) {
                $api->delete('del_enterprise', 'EnterpriseController@delEnterprise')->name('根据ID删除企业咨询订单');
                $api->get('get_order_enterprise_list', 'EnterpriseController@getOrderEnterpriseList')->name('获取企业咨询订单列表');
                $api->post('set_enterprise_order', 'EnterpriseController@setEnterpriseOrder')->name('设置企业咨询订单状态');
                $api->get('get_enterprise_order_list', 'EnterpriseController@getEnterpriseOrderList')->name('获取本人企业咨询订单列表(后端)');
            });
        });

        //项目对接模块
        $api->group(['prefix' => 'project', 'namespace' => 'Project'], function ($api){
            #成员使用路由
            $api->group(['middleware' => 'member.jwt.auth'],function($api) {
                $api->get('get_project_list', 'ProjectController@getProjectList')->name('获取项目对接订单列表');
                $api->get('get_project_info', 'ProjectController@getProjectInfo')->name('获取项目对接订单信息');
                $api->post('add_project', 'ProjectController@addProject')->name('添加项目对接订单');
                $api->post('upd_project', 'ProjectController@updProject')->name('修改项目对接订单');
                $api->delete('del_project', 'ProjectController@delProject')->name('删除项目对接订单');
            });

            #OA 员工使用项目对接路由
            $api->group(['middleware' => ['oa.jwt.auth','oa.perm']],function($api) {
                $api->get('get_project_order_list','OaProjectController@getProjectOrderList')->name('获取项目对接订单列表');
                $api->get('get_project_order_info','OaProjectController@getProjectOrderInfo')->name('获取项目对接订单信息');
                $api->post('set_project_order_status','OaProjectController@setProjectOrderStatus')->name('设置项目对接订单状态');

            });
        });


        //商城模块
        $api->group(['prefix' => 'shop', 'namespace' => 'Shop'], function ($api){
            #OA 商城后台
            $api->group(['middleware' => ['oa.jwt.auth','oa.perm']],function($api) {
                $api->post('add_activity_goods','ActivityController@addActivityGoods')->name('添加活动商品');
                $api->post('edit_activity_goods','ActivityController@editActivityGoods')->name('修改活动商品');
                $api->get('get_activity_goods_list','ActivityController@getActivityGoodsList')->name('获取活动商品列表');

                $api->post('add_goods','OaGoodsController@addGoods')->name('添加商品');
                $api->delete('delete_goods','OaGoodsController@deleteGoods')->name('删除商品');
                $api->post('is_putaway_goods','OaGoodsController@isPutaway')->name('上下架商品');
                $api->post('edit_goods','OaGoodsController@editGoods')->name('修改商品');
                $api->get('goods_list','OaGoodsController@goodsList')->name('获取商品列表');
                $api->get('get_goods_detail','OaGoodsController@getGoodsDetail')->name('获取商品详情');
                $api->get('list_shop_car','ShopCarController@listShopCar')->name('OA获取购物车列表');

                $api->post('add_category','OaGoodsCategoryController@addCategory')->name('添加商品类别');
                $api->delete('delete_category','OaGoodsCategoryController@deleteCategory')->name('删除商品类别');
                $api->post('edit_category','OaGoodsCategoryController@editCategory')->name('修改商品类别');
                $api->get('get_category_list','OaGoodsCategoryController@getCategoryList')->name('获取商品类别列表');

                $api->post('add_announce','OaAnnounceController@addAnnounce')->name('添加首页公告');
                $api->delete('delete_announce','OaAnnounceController@deleteAnnounce')->name('删除公告');
                $api->post('edit_announce','OaAnnounceController@editAnnounce')->name('修改公告');
                $api->get('get_announce_list','OaAnnounceController@getAnnounceList')->name('获取公告列表');

                $api->get('get_shop_order_list','OaOrderController@getShopOrderList')->name('获取商城订单列表');
                $api->get('get_order_detail','OaOrderController@getOrderDetail')->name('获取订单详情');
                $api->post('shipment','OaOrderController@shipment')->name('发货');
                $api->get('get_oa_order_express_details','OaOrderController@getOaOrderExpressDetails')->name('Oa根据订单号获取物流状态');
                $api->get('get_express_list','OaOrderController@getExpressList')->name('OA获取快递列表');

            });
            $api->group(['middleware' => 'member.jwt.auth'],function($api) {
                $api->post('add_shop_car','ShopCarController@addShopCar')->name('用户添加商品至购物车');
                $api->delete('del_shop_car','ShopCarController@delShopCar')->name('用户删除购物车商品');
                $api->post('change_car_num','ShopCarController@changeCarNum')->name('用户添加购物车商品数量');
                $api->get('shop_car_list','ShopCarController@shopCarList')->name('用户获取购物车商品列表');

                $api->get('get_place_order_detail','OrderController@getPlaceOrderDetail')->name('获取下单详情');
                $api->post('submit_order','OrderController@submitOrder')->name('提交订单');
                $api->post('goods_receiving','OrderController@goodsReceiving')->name('确认收货');
                $api->post('cancel_order','OrderController@cancelOrder')->name('取消订单');
                $api->get('get_my_order_list','OrderController@getMyOrderList')->name('获取我的订单列表');
                $api->post('order_detail','OrderController@orderDetail')->name('获取订单详情');
                $api->post('get_order_express_details','OrderController@getOrderExpressDetails')->name('用户根据订单号获取物流状态');
                $api->get('order_detail','OrderController@orderDetail')->name('获取订单详情');
                $api->get('get_goods_details','GoodsController@getGoodsDetails')->name('用户获取商品详情');

                $api->get('get_home','GoodsController@getHome')->name('获取首页');
                $api->get('get_goods_list','GoodsController@getGoodsList')->name('获取商品列表');
                $api->get('category_list','GoodsController@getCategoryList')->name('获取商品分类列表');
                $api->get('get_goods_spec','GoodsController@getGoodsSpec')->name('获取商品规格');
            });
        });

        //积分
        $api->group(['prefix' => 'score', 'namespace' => 'Score'], function ($api){
            #OA 积分后台
            $api->group(['middleware' => ['oa.jwt.auth','oa.perm']],function($api) {
                $api->post('give_score','OaScoreController@giveScore')->name('赠送积分');
                $api->get('get_score_record_list','OaScoreController@getScoreRecordList')->name('获取积分记录列表');

                $api->get('get_score_category_list','OaScoreController@getScoreCategoryList')->name('获取积分分类列表');
                $api->post('add_score_category','OaScoreController@addScoreCategory')->name('添加积分分类');
                $api->post('open_or_close','OaScoreController@openOrClose')->name('开启或关闭积分分类');
            });
        });
        //七牛云
        $api->group(['prefix' => 'qiniu'], function ($api){
            //$api->get('images_migration', 'QiNiuController@imagesMigration')->name('本地图片迁移至七牛云');
            $api->post('upload_images', 'QiNiuController@uploadImages')->name('上传图片至七牛云');
        });

        //公共模块
        $api->group(['prefix' => 'common', 'namespace' => 'Common'], function ($api){
            $api->post('send_captcha', 'CommonController@sendCaptcha')->name('发送短信验证码');
            $api->get('browser_push', 'MessageController@browserPush')->name('浏览器推送消息');
            $api->post('mobile_exists', 'CommonController@mobileExists')->name('检测成员手机号是否注册');
            $api->get('get_area_list', 'AreaController@getAreaList')->name('获取省市区街道四级联动地区列表');
            $api->get('home', 'CommonController@home')->name('获取首页');
            $api->group(['middleware' => ['oa.jwt.auth','oa.perm']],function($api) {
                $api->post('add_home_banner', 'BannerController@addBanners')->name('添加首页banner');
                $api->delete('delete_banner', 'BannerController@deleteBanner')->name('删除banner图');
                $api->post('edit_banners', 'BannerController@editBanners')->name('修改首页banner');
                $api->get('get_banner_list', 'BannerController@getBannerList')->name('获取首页banner图列表');

                $api->get('get_image_repository', 'ImagesController@getImageRepository')->name('获取图片仓库');
            });
            $api->group(['middleware' => 'member.jwt.auth'],function($api) {
                $api->post('is_collect', 'CommonController@isCollect')->name('收藏或取消收藏');
                $api->post('get_express_details', 'ExpressController@getExpressDetails')->name('用户获取订单物流状态');
            });
        });
        //支付模块模块
        $api->group(['prefix' => 'payments', 'namespace' => 'Pay'], function ($api){
            $api->post('we_chat_pay', 'WeChatPayController@weChatPay')->name('微信小程序微信支付下单接口');
        });
    });
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
