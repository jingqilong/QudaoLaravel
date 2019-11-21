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
    }
}
