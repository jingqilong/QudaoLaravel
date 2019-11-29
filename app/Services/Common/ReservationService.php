<?php


namespace App\Services\Common;


use App\Services\BaseService;
use App\Services\Enterprise\OrderService as EnterpriseOrderService;
use App\Services\House\ReservationService as HouseReservationService;
use App\Services\Loan\PersonalService as LoanPersonalService;
use App\Services\Medical\OrdersService as MedicalOrdersService;
use App\Services\Prime\ReservationService as PrimeReservationService;
use App\Services\Project\OaProjectService as ProjectProjectService;
use App\Traits\HelpTrait;

class ReservationService extends BaseService
{
    use HelpTrait;
    /**
     * 获取预约统计数据
     * @return array
     */
    public function getReservationNumber(){
        $res = [
            'consult'   => EnterpriseOrderService::getStatistics(),
            'house'     => HouseReservationService::getStatistics(),
            'loan'      => LoanPersonalService::getStatistics(),
            'medical'   => MedicalOrdersService::getStatistics(),
            'prime'     => PrimeReservationService::getStatistics(),
            'project'   => ProjectProjectService::getStatistics(),
        ];
        $res['all']     =  [
            'total'     => $this->arrayFieldSum($res,'total'),
            'audit'     => $this->arrayFieldSum($res,'audit'),
            'no_audit'  => $this->arrayFieldSum($res,'no_audit'),
            'cancel'    => $this->arrayFieldSum($res,'cancel'),
        ];
        $this->setMessage('获取成功！');
        return $res;
    }
}