<?php
namespace App\Services\Medical;


use App\Repositories\MedicalDoctorsRepository;
use App\Repositories\MediclaHospitalsRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;

class OrdersService extends BaseService
{
    use HelpTrait;

    public function addDoctors($request)
    {
        if (!MediclaHospitalsRepository::getOne($request(['hospitals_id']))){
            $this->setError('医院不存在！');
            return false;
        }
        if (!MedicalDoctorsRepository::getOne($request(['doctor_id']))){
            $this->setError('医生不存在！');
            return false;
        }
        $add_arr = [
            'name'      =>  $request['name'],
        ];
    }
}
            