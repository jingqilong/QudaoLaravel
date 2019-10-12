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
                $api->get('get_depart','OaController@getDepart')->name("获取部门");
                $api->post('add_depart','OaController@addDepart')->name("添加部门");
                $api->post('update_depart','OaController@updateDepart')->name("修改部门");
                $api->delete('del_depart','OaController@delDepart')->name("删除部门");
                $api->post('get_depart_list','OaController@getDepartList')->name("获取部门列表");
                #OA员工
                $api->post('get_employee_list','EmployessController@getEmployeeList')->name('获取员工列表');
                $api->get('get_employee_info','EmployessController@getEmployeeInfo')->name('获取员工信息');
                $api->post('add_employee','EmployessController@addEmployee')->name('添加员工信息');
                $api->delete('del_employee','EmployessController@delEmployee')->name("删除员工");
                $api->post('update_employee','EmployessController@updateEmployee')->name("更新员工信息");
                $api->post('add_push_auth','MessageController@addPushAuth')->name("添加web推送授权信息");
                #OA权限管理
                $api->post('add_menu','PermissionsController@addMenu')->name("添加菜单");
                $api->post('add_permission','PermissionsController@addPermission')->name("添加权限");
                $api->post('add_roles','PermissionsController@addRoles')->name("添加角色");
                $api->post('add_user','PermissionsController@addUser')->name("添加用户");
                $api->get('menu_list','PermissionsController@menuList')->name("获取菜单列表");
                $api->get('user_list','PermissionsController@userList')->name("获取用户列表");
                $api->get('permission_list','PermissionsController@permissionList')->name("获取权限列表");
                $api->get('role_list','PermissionsController@roleList')->name("获取角色列表");
                $api->get('operation_log','PermissionsController@operationLog')->name("获取操作日志");
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
            $api->post('add_employee','EmployessController@addEmployee')->name("添加员工");
        });
        //精选服务模块
        $api->group(['prefix' => 'prime','namespace' => 'Prime'],function ($api){
            $api->group(['middleware' => 'prime.jwt.auth'],function($api){
                $api->post('logout','PrimeController@logout')->name('退出');
                $api->post('refresh','PrimeController@refresh')->name('刷新token');
                $api->get('get_user_info','PrimeController@getUserInfo')->name('获取用户信息');
            });
            $api->post('login','PrimeController@login')->name('登录');
        });
        //会员模块
        $api->group(['prefix' => 'member','namespace' => 'Member'],function ($api){
            $api->group(['middleware' => 'member.jwt.auth'],function($api){
                $api->post('logout','MemberController@logout')->name('退出');
                $api->post('refresh','MemberController@refresh')->name('刷新token');
                $api->get('get_user_info','MemberController@getUserInfo')->name('获取用户信息');
                $api->get('get_user_list','MemberController@getUserList')->name('获取用户列表');
                $api->get('update_user_info','MemberController@updateUserInfo')->name('更改用户信息');
                $api->any('update_user_password','MemberController@updateUserPassword')->name('更改用户密码');
                $api->any('sms_update_user_password','MemberController@forgetPassword')->name('短信验证码修改密码');
                $api->post('update_user_password','MemberController@updateUserPassword')->name('更改用户密码');
                $api->get('get_relation_list','MemberController@getRelationList')->name('获取用户推荐关系');
                $api->get('promote_qr_code','PublicController@promoteQrCode')->name('获取推广二维码');

                #会员权限
                $api->post('add_service','ServiceController@addService')->name('添加服务');
                $api->get('service_detail','ServiceController@serviceDetail')->name('获取服务详情');
                $api->post('edit_service','ServiceController@editService')->name('修改服务');
                $api->delete('delete_service','ServiceController@deleteService')->name('删除服务');
                $api->get('service_list','ServiceController@serviceList')->name('获取服务列表');
                $api->post('grade_add_service','ServiceController@gradeAddService')->name('给等级添加服务');
                $api->delete('grade_delete_service','ServiceController@gradeDeleteService')->name('删除等级中的服务');
                $api->post('grade_edit_service','ServiceController@gradeEditService')->name('修改等级与服务对应关系');
                $api->get('grade_service_detail','ServiceController@gradeServiceDetail')->name('获取等级下的服务详情');
                $api->post('add_view_member','ServiceController@addViewMember')->name('添加会员可查看成员');
                $api->delete('delete_view_member','ServiceController@deleteViewMember')->name('软删除会员可查看成员');
                $api->post('restore_view_member','ServiceController@restoreViewMember')->name('恢复会员可查看成员');
            });
            $api->post('login','MemberController@login')->name('登录');
            $api->post('sms_login','MemberController@smsLogin')->name('短信验证登录');
            $api->post('mini_login','Member\WeChatController@miniLogin')->name('微信小程序登录');
            $api->post('mini_bind_mobile','Member\WeChatController@miniBindMobile')->name('微信小程序绑定手机号');
            $api->post('add_loan', 'LoanController@addLoan')->name('添加贷款订单');
        });

        //房产模块
        $api->group(['prefix' => 'house', 'namespace' => 'House'], function ($api){
            $api->group(['middleware' => 'member.jwt.auth'],function($api) {
                $api->post('add_house_order', 'HouseController@sendCaptcha')->name('增加房产订单');
            });
        });

        //贷款模块
        $api->group(['prefix' => 'loan', 'namespace' => 'Loan'], function ($api){
            $api->group(['middleware' => 'member.jwt.auth'],function($api) {
                $api->post('add_loan', 'LoanController@addLoan')->name('添加贷款订单');
                $api->post('upd_loan', 'LoanController@updLoan')->name('修改贷款订单');
                $api->delete('del_loan', 'LoanController@delLoan')->name('删除贷款订单');
                $api->get('get_loan_list', 'LoanController@getLoanList')->name('获取贷款订单');
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
