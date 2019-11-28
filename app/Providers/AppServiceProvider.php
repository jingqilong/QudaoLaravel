<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191); //add fixed sql
        //检查浮点数
        Validator::extend('float', function($attribute,$value, $parameters,$validator) {
            if (!is_numeric($value)){
                return false;
            }
            $value = (float)$value;
            return is_float($value);
        });
        //检查手机号
        Validator::extend('mobile', function($attribute,$value, $parameters,$validator) {
            $mobile_regex = '/^(1(([35789][0-9])|(47)))\d{8}$/';
            if (!preg_match($mobile_regex, $value)) {
                return false;
            }
            return true;
        });
    }
}
