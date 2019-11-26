<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tolawho\Loggy\Facades\Loggy;

class TestController extends Controller
{
    public function index(){
        Loggy::write('event','This is a test log.');
        return ['code' => 200];
    }
}
