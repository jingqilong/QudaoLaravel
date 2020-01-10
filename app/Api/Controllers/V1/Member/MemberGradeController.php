<?php


namespace App\Api\Controllers\V1\Member;


use App\Api\Controllers\ApiController;
use App\Services\Member\MemberGradeServices;

class MemberGradeController extends ApiController
{
    public $memberGradeServices;

    /**
     * MemberGradeController constructor.
     * @param $memberGradeServices
     */
    public function __construct(MemberGradeServices $memberGradeServices)
    {
        parent::__construct();
        $this->memberGradeServices = $memberGradeServices;
    }
}