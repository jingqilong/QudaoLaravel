<?php


namespace App\Api\Controllers\V1\Medical;


use App\Api\Controllers\ApiController;
use App\Services\Medical\DoctorService;

class DoctorController extends ApiController
{
    public $doctorService;

    /**
     * DoctorController constructor.
     * @param $doctorService
     */
    public function __construct(DoctorService $doctorService)
    {
        parent::__construct();
        $this->doctorService = $doctorService;
    }

}