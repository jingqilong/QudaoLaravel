<?php


namespace App\Api\Controllers\V1\Prime;


use App\Api\Controllers\ApiController;
use App\Services\Prime\ReservationService;

class PrimeController extends ApiController
{
    protected $reservationService;

    /**
     * TestApiController constructor.
     * @param ReservationService $reservationService
     */
    public function __construct(ReservationService $reservationService)
    {
        parent::__construct();
        $this->reservationService = $reservationService;
    }

}