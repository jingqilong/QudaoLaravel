<?php

namespace App\Traits;
use App\Enums\ProcessGetwayTypeEnum;
use App\Repositories\OaProcessCategoriesRepository;
use Ixudra\Curl\Facades\Curl;
use Tolawho\Loggy\Facades\Loggy;

/**
 * Trait ProcessTrait
 * @package App\Traits
 * @desc 这是给流程引擎用的Trait
 */
trait ProcessTrait
{
    /**
     * @param $process_category_id
     * @param $business_id
     * @return mixed;
     */
    public function getDataFromGetWayById($process_category_id,$business_id){
        $process_category_detail =  OaProcessCategoriesRepository::getOne(['id'=>$process_category_id],['getway_type','getway_name']);
        if(!$process_category_detail){
            return false;
        }
        $getway_type = $process_category_detail['getway_type'];
        $getway_config = $process_category_detail['getway_naem'];
        //从REPOSITORY读取数据
        if(ProcessGetwayTypeEnum::REPOSITORY == $getway_type){
            list($class, $function) = explode(".",$getway_config);
            try{
                $obj = App()->make($class);
            }catch(\Exception $e){
                Loggy::write("error","ProcessTrait::getDataFromGetWayById->with REPOSITORY error",$e);
            }
            return CalL_user_func_array([$obj, $function],$business_id);
        }
        //从SERVICE读取数据
        if(ProcessGetwayTypeEnum::SERVICE == $getway_type){
            list($class, $function) = explode(".",$getway_config);
            try{
                $obj = App()->make($class);
            }catch(\Exception $e){
                Loggy::write("error","ProcessTrait::getDataFromGetWayById->with REPOSITORY error",$e);
            }
            return CalL_user_func_array([$obj, $function],$business_id);
        }
        //从RESOURCE读取数据
        if(ProcessGetwayTypeEnum::RESOURCE == $getway_type){
            $target_url = url(sprintf($getway_config,$business_id));
            $response = Curl::to($target_url)
                ->returnResponseObject()
                ->get();

            if(200 != $response->status){
                Loggy::write('error',$response->error);
                return false;
            }
            return $response;
        }
        //从ROUTE读取数据
        if(ProcessGetwayTypeEnum::ROUTE == $getway_type){
            $target_url = url(sprintf($getway_config,$business_id));
            $response = Curl::to($target_url)
                ->returnResponseObject()
                ->get();

            if(200 != $response->status){
                Loggy::write('error',$response->error);
                return false;
            }
            return $response;
        }
        Loggy::write('error','流程网关类型不对！');
        return false;
    }


}