<?php

namespace App\Api\Controllers\V1;

use App\Api\Controllers\ApiController;
use App\Events\SendFlowSms;
use App\Events\SendSiteMessage;
use App\Exceptions\ServiceException\EventDoesNotExistsException;
use App\Library\UmsPay\UmsPay;
use App\Mail\DingTalkEmail;
use App\Repositories\ActivityDetailRepository;
use App\Repositories\MemberInfoRepository;
use App\Services\Common\EventProcessorService;
use App\Services\Common\QiNiuService;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use EasyWeChat\Kernel\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Ixudra\Curl\Facades\Curl;

use App\Repositories\MemberGradeDefineRepository;


class TestApiController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/test/test",
     *     tags={"测试"},
     *     summary="这是一个测试接口🐀🐂🐅🐇🐉🐍🐎🐏🙈🐓🐕🐖",
     *     description="sang" ,
     *     operationId="test",
     *     @OA\Parameter(
     *         name="test",
     *         in="query",
     *         description="测试参数",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="integer",
     *         in="query",
     *         description="整型参数",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="long",
     *         in="query",
     *         description="长整型参数",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="float",
     *         in="query",
     *         description="浮点型参数",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="double",
     *         in="query",
     *         description="双精度浮点型参数",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="byte",
     *         in="query",
     *         description="字节型参数",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="binary",
     *         in="query",
     *         description="二进制参数",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="boolean",
     *         in="query",
     *         description="布尔值参数",
     *         required=true,
     *         @OA\Schema(
     *             type="boolean",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="时间参数",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="dateTime",
     *         in="query",
     *         description="日期时间型参数",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Invalid input",
     *     ),
     * )
     *
     */
    public function index(){


//        try{
//            return EventProcessorService::eventReceiver('send_sms','18394377667','测试短信');
//        } catch (EventDoesNotExistsException $e) {
//            return $e->getMessage();
//        }

//        $config = config('wechat.official_account.default');
//        $app = Factory::officialAccount($config);
//        try {
//            return $app->customer_service->list();
//        } catch (InvalidConfigException $e) {
//            return '无效的配置异常';
//        }

//        $qiniu = new QiNiuService();
//        return $qiniu->migrationBigImage();
//        return $qiniu->uploadQiniu('Goods','主图.jpg','C:\phpStudy\PHPTutorial\WWW\QudaoLaravel\public\upload\主图.jpg');
    }


    /**
     * 批量生成模型
     */
    /**
     * @OA\Get(
     *     path="/api/v1/test/create_model",
     *     tags={"测试"},
     *     summary="批量生成模型",
     *     description="sang" ,
     *     operationId="create_model",
     *
     *     @OA\Response(
     *         response=100,
     *         description="生成失败",
     *     ),
     * )
     *
     */
    public function createModel(){
        $data = [];
        $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
        foreach ($tables as $k => &$v){
            $arr = [];
            $arr = explode('_',$v);
            if (count($arr) == 1){
                unset($tables[$k]);
                continue;
            }
            if (!in_array('qd',$arr) || 'admin' == $arr[1]){
                unset($tables[$k]);
                continue;
            }
            $v = str_replace('qd_','',$v);
        }
        foreach ($tables as $table) {
            $columns = Schema::getColumnListing($table);
            $primaryKey = $columns[0];
            $columns = implode("','",$columns);
            $columns = "['$columns']";
            $class = $table;
            $tables = $table;
            $class = explode('_', $class);
            $className = '';
            if (count($class) == 1) {
                $className = $class[0];
            } else {
                for ($i = 1; $i < count($class); $i++) {
                    $className .= ucwords($class[$i]);
                }
            }
            $path = 'Models';
            $path = app_path($path) . "\\";
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            } else {
                chmod($path, 0777);
            }
            $arr = explode('_',$table);
            $name = '';
            foreach ($arr as $v){
                $name .= ucwords($v);
            }
            $className = $name.'Model';
            $file = $className . '.php';

            if (file_exists($path . $file)) {
                $data[] = $className.'已存在！';
                continue;
            }
            $r = fopen($path . $file, 'w');
//            $dir = ucwords($class[0]);

            $text = "<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class $className extends Model
{

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected \$table = '$tables';

     /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public \$timestamps = false;

    /**
     * 数据表中的主键
     *
     * @var bool
     */
    protected \$primaryKey = '$primaryKey';


    protected \$fillable = $columns;



}

 ";
            fwrite($r, $text);
            fclose($r);

            $data[] = $className.'创建完成！';
        }
        return ['code' => 200, 'message' => '生成成功', 'data' => $data];
    }


    /**
     * 生成Repository
     */
    /**
     * @OA\Get(
     *     path="/api/v1/test/create_repository",
     *     tags={"测试"},
     *     summary="批量生成Repository",
     *     description="sang" ,
     *     operationId="create_repository",
     *
     *     @OA\Response(
     *         response=100,
     *         description="生成失败",
     *     ),
     * )
     *
     */
    public function createRepository(){
        $data = [];
        $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
        foreach ($tables as $k => &$v){
            $arr = [];
            $arr = explode('_',$v);
            if (count($arr) == 1){
                unset($tables[$k]);
                continue;
            }
            if (!in_array('qd',$arr) || 'admin' == $arr[1]){
                unset($tables[$k]);
                continue;
            }
            $v = str_replace('qd_','',$v);
        }
        foreach ($tables as $table) {
            $class = $table;
            $tables = $table;
            $class = explode('_', $class);
            $className = '';
            if (count($class) == 1) {
                $className = $class[0];
            } else {
                for ($i = 1; $i < count($class); $i++) {
                    $className .= ucwords($class[$i]);
                }
            }

            $path = 'Repositories';
            $path = app_path($path) . "\\";
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            } else {
                chmod($path, 0777);
            }
            $arr = explode('_',$table);
            $name = '';
            foreach ($arr as $v){
                $name .= ucwords($v);
            }
            $className = $name.'Model';
            $repositoryName = $name.'Repository';

            $file = $repositoryName . '.php';

            if (file_exists($path . $file)) {
                $data[] = $tables.'已存在！';
                continue;
            }

            $r = fopen($path . $file, 'w');

            $text = "<?php


namespace App\Repositories;


use App\Models\\$className;
use App\Repositories\Traits\RepositoryTrait;

class $repositoryName extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param \$model
     */
    public function __construct($className \$model)
    {
        \$this->model = \$model;
    }
}
            ";
            fwrite($r, $text);
            fclose($r);
            $data[] = $repositoryName.'创建完成! ';
        }
        return ['code' => 200, 'message' => '生成成功', 'data' => $data];
    }

    /**
     * 生成Services
     */
    /**
     * @OA\Get(
     *     path="/api/v1/test/create_service",
     *     tags={"测试"},
     *     summary="批量生成Services",
     *     description="sang" ,
     *     operationId="create_service",
     *
     *     @OA\Response(
     *         response=100,
     *         description="生成失败",
     *     ),
     * )
     *
     */
    public function createService(){
        $data = [];
        $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
        $modules = [];
        foreach ($tables as $k => &$v){
            $arr = [];
            $arr = explode('_',$v);
            if (count($arr) == 1){
                unset($tables[$k]);
                continue;
            }
            if (!in_array('qd',$arr) || 'admin' == $arr[1]){
                unset($tables[$k]);
                continue;
            }
            $v = str_replace('qd_','',$v);
            $modules[ucwords($arr[1])][] = $v;
        }
        foreach ($modules as $module => $service){
            foreach ($service as $v){
                $class = $v;
                $class = explode('_', $class);
                $serviceName = '';
                if (count($class) == 1) {
                    $serviceName = ucwords($class[0]);
                } else {
                    for ($i = 1; $i < count($class); $i++) {
                        $serviceName .= ucwords($class[$i]);
                    }
                }
                $serviceName = $serviceName."Service";
                $path = 'Services\\'.$module;

                $path = app_path($path) . "\\";
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                } else {
                    chmod($path, 0777);
                }
                $arr = explode('_',$v);
                $name = '';
                foreach ($arr as $value){
                    $name .= ucwords($value);
                }

                $file = $serviceName . '.php';

                if (file_exists($path . $file)) {
                    $data[] = $module.'\\'.$v.' 已存在！';
                    continue;
                }

                $r = fopen($path . $file, 'w');

                $text = "<?php
namespace App\Services\\$module;


use App\Services\BaseService;

class $serviceName extends BaseService
{

}
            ";
                fwrite($r, $text);
                fclose($r);

                $data[] = $serviceName.'创建完成！';
            }
        }
        return ['code' => 200, 'message' => '生成成功', 'data' => $data];
    }

    /**
     * createOrder
     */
    /**
     * @OA\Get(
     *     path="/api/v1/test/create_order",
     *     tags={"测试"},
     *     summary="createOrder",
     *     description="sang" ,
     *     operationId="create_order",
     *
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     *
     */
    public function createOrder(){
        $order_no = date("Ymdhis");
        $umsPay = new UmsPay();
        $response = $umsPay->createOrder($order_no,0.01);
        return ['code' => 200, 'message' => '成功', 'data' => $response];
    }
    /**
     * queryClearDate
     */
    /**
     * @OA\Get(
     *     path="/api/v1/test/query_clear_date",
     *     tags={"测试"},
     *     summary="queryClearDate",
     *     description="sang" ,
     *     operationId="query_clear_date",
     *
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     *
     */
    public function queryClearDate(){
        $order_no = date("Ymdhis");
        $umsPay = new UmsPay();
        $response = $umsPay->queryClearDate("201901041549161","20190104");
        return ['code' => 200, 'message' => '成功', 'data' => $response];
    }
    /**
     * queryTransDate
     */
    /**
     * @OA\Get(
     *     path="/api/v1/test/query_trans_date",
     *     tags={"测试"},
     *     summary="queryTransDate",
     *     description="sang" ,
     *     operationId="query_trans_date",
     *
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     *
     */
    public function queryTransDate(){
        $order_no = date("Ymdhis");
        $umsPay = new UmsPay();
        $response = $umsPay->queryTransDate("201901041549161","20190104");
        return ['code' => 200, 'message' => '成功', 'data' => $response];
    }
    /**
     * queryBySystemCode
     */
    /**
     * @OA\Get(
     *     path="/api/v1/test/query_by_system_code",
     *     tags={"测试"},
     *     summary="queryBySystemCode",
     *     description="sang" ,
     *     operationId="query_by_system_code",
     *
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     *
     */
    public function queryBySystemCode(){
        $order_no = date("Ymdhis");
        $umsPay = new UmsPay();
        $response = $umsPay->queryBySystemCode("21190122100423194476");
        return ['code' => 200, 'message' => '成功', 'data' => $response];
    }
    /**
     * refund
     */
    /**
     * @OA\Get(
     *     path="/api/v1/test/refund",
     *     tags={"测试"},
     *     summary="refund",
     *     description="sang" ,
     *     operationId="refund",
     *
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     *
     */
    public function refund(){
        $order_no = date("Ymdhis");
        $umsPay = new UmsPay();
        $response = $umsPay->refund("21190122100423194476");
        return ['code' => 200, 'message' => '成功', 'data' => $response];
    }

    public function eventTest(){
        $email_data = [
            'event_type'        => 1,
            'receiver_email'    => '2286760960@qq.com',
            'title'             => '普通邮件',
            'receiver_name'     => '桑',
//            'subcopy'           => '--',
            'process_full_name' => '--',
            'link_url'          => 'https://fanmmy.cn',
            'precess_result'    => 'ok',
        ];
        $mailable = new DingTalkEmail($email_data);
        Mail::to('2286760960@qq.com')->send($mailable);
        return 'ok';
    }

    /**
     * refund
     */
    /**
     * @OA\Get(
     *     path="/api/v1/test/enum_repository_test",
     *     tags={"测试"},
     *     summary="EnumRepositoryTest",
     *     description="bardo" ,
     *     operationId="EnumRepositoryTest",
     *
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     *
     */
    public function EnumRepositoryTest(){
        $data = [
            'grade_from_mehtod' =>
                [
                    'code' =>  'MemberGradeDefineRepository::DEFAULT()',
                    'value' =>MemberGradeDefineRepository::DEFAULT()
                 ],
            'grade_get_label' =>
                [
                    'code' =>  'MemberGradeDefineRepository::getLabelByid(MemberGradeDefineRepository::DEFAULT())',
                    'value' =>MemberGradeDefineRepository::getLabelByid(MemberGradeDefineRepository::DEFAULT())
                ],
            'grade_one_by_id' =>
                [
                    'code' =>  'MemberGradeDefineRepository::getLabelByid(MemberGradeDefineRepository::DEFAULT())',
                    'value' =>MemberGradeDefineRepository::getOneById(MemberGradeDefineRepository::DEFAULT())
                ],


        ];
        return ['code' => 200, 'message' => '成功', 'data' => $data];

    }
}
