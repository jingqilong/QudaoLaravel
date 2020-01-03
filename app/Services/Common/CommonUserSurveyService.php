<?php


namespace App\Services\Common;


use App\Enums\CommonGenderEnum;
use App\Enums\UserSurveyHearFromEnum;
use App\Repositories\CommonUserSurveyRepository;
use App\Services\BaseService;
use App\Services\Oa\EmployeeService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;

class CommonUserSurveyService extends BaseService
{
    use HelpTrait;

    /**
     * 提交用户调研
     * @param $request
     * @return bool
     */
    public function submitUserSurvey($request){
        $add_arr = [
            'name'      => $request['name'],
            'gender'    => $request['gender'],
            'mobile'    => $request['mobile'],
            'hear_from' => $request['hear_from'],
            'request'   => $request['request'],
            'created_at'=> time(),
            'updated_at'=> time()
        ];
        if (!CommonUserSurveyRepository::getAddId($add_arr)){
            $this->setError('提交失败！');
            return false;
        }
        $this->setMessage('提交成功！');
        return true;
    }

    /**
     * 删除用户调研
     * @param $id
     * @return bool
     */
    public function deleteUserSurvey($id){
        if (!CommonUserSurveyRepository::exists(['id' => $id])){
            $this->setError('该记录不存在！');
            return false;
        }
        if (!CommonUserSurveyRepository::delete(['id' => $id])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功！');
        return true;
    }

    /**
     * 设置用户调研记录状态
     * @param $id
     * @param $status
     * @return bool
     */
    public function setStatus($id, $status){
        $employee = Auth::guard('oa_api')->user();
        if (!CommonUserSurveyRepository::exists(['id' => $id])){
            $this->setError('该记录不存在！');
            return false;
        }
        $upd_arr = [
            'status'        => $status,
            'updated_by'    => $employee->id,
            'updated_at'    => time()
        ];
        if (!CommonUserSurveyRepository::getUpdId(['id' => $id],$upd_arr)){
            $this->setError('操作失败！');
            return false;
        }
        $this->setMessage('操作成功！');
        return true;
    }

    /**
     * 获取用户调研列表
     * @param $request
     * @return bool|mixed|null
     */
    public function getUserSurveyList($request){
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $keywords   = $request['keywords'] ?? null;
        $hear_from  = $request['hear_from'] ?? null;
        $status     = $request['status'] ?? null;
        $where      = ['id' => ['<>',0]];
        $sort       = 'id';
        $asc        = 'desc';
        $column     = ['*'];
        if (!is_null($hear_from)){
            $where['hear_from'] = $hear_from;
        }
        if (!is_null($status)){
            $where['status'] = $status;
        }
        if (!empty($keywords)){
            $keyword = [$keywords => ['name','mobile','request']];
            if (!$list = CommonUserSurveyRepository::search($keyword,$where,$column,$page,$page_num,$sort,$asc)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = CommonUserSurveyRepository::getList($where,$column,$sort,$asc,$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无信息！');
            return $list;
        }
        $list['data'] = EmployeeService::getListOperationByName($list['data'],['updated_by' => 'updated_by_name']);
        foreach ($list['data'] as &$value){
            $value['gender']        = CommonGenderEnum::getLabel($value['gender']);
            $value['hear_from']     = UserSurveyHearFromEnum::getLabel($value['hear_from']);
            $value['created_at']    = date('Y-m-d H:i:s',$value['created_at']);
            $value['updated_at']    = date('Y-m-d H:i:s',$value['updated_at']);
            unset($value['updated_by']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 获取获知渠道列表
     * @return array
     */
    public function getHearFromList()
    {
        $list = [];
        foreach (UserSurveyHearFromEnum::$labels as $const => $label) {
            $list[] = [
                'id'    => UserSurveyHearFromEnum::getConst($const),
                'label' => $label
            ];
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}