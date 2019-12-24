<?php
namespace App\Services\Oa;


use App\Repositories\MemberPreferenceTypeRepository;
use App\Repositories\MemberPreferenceRepository;
use App\Repositories\MemberPreferenceValueRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\DB;

class PreferenceService extends BaseService
{
    use HelpTrait;

    /**
     * 添加成员简历偏好类别
     * @param $request
     * @return bool
     */
    public function addPreferenceType($request)
    {
        if (MemberPreferenceTypeRepository::exists(['name' => [$request['name']]])){
            $this->setError('类别名称已存在!');
            return false;
        }
        $add_arr = [
            'name'      =>  $request['name'],
            'content'   =>  $request['content'] ?? '',
            'create_at' =>  time(),
            'updated_at'=>  time(),
        ];
        if (!MemberPreferenceTypeRepository::getAddId($add_arr)){
            $this->setError('添加失败!');
            return false;
        }
        $this->setMessage('添加成功!');
        return true;
    }


    /**
     * 删除偏好类别 （未使用则全部删除，使用中则不删除）
     * @param $request
     * @return bool
     */
    public function delPreferenceType($request)
    {
        if (!MemberPreferenceTypeRepository::exists(['id' => [$request['id']]])){
            $this->setError('类别名称不存在!');
            return false;
        }
        if (MemberPreferenceRepository::exists(['type' => [$request['id']]])){
            $this->setError('该类别名称使用中,不能被删除!');
            return false;
        }
        DB::beginTransaction();
        if (!MemberPreferenceTypeRepository::delete(['id' => $request['id']])){#类型表删除
            $this->setError('删除失败!');
            DB::rollBack();
            return false;
        }
        if (MemberPreferenceRepository::exists(['type' => $request['id']])) if (!MemberPreferenceRepository::delete(['type' => $request['id']])){#业务表数据删除
            $this->setError('删除失败!');
            DB::rollBack();
            return false;
        }
        if (MemberPreferenceValueRepository::exists(['type_id' => $request['id']])) if (!MemberPreferenceValueRepository::delete(['type_id' => $request['id']])){#类型数据值表删除
            $this->setError('删除失败!');
            DB::rollBack();
            return false;
        }
        DB::commit();
        $this->setMessage('删除成功!');
        return true;
    }

    /**
     * 修改偏好类别
     * @param $request
     * @return bool
     */
    public function editPreferenceType($request)
    {
        if (!MemberPreferenceTypeRepository::exists(['id' => [$request['id']]])){
            $this->setError('类别不存在!');
            return false;
        }
        if (MemberPreferenceTypeRepository::exists(array_merge(['id' => ['<>',$request['id']]],['name' => $request['name']]))){
            $this->setError('类别名称已存在!');
            return false;
        }
        $upd_arr = [
            'name'         =>  $request['name'],
            'content'      =>  $request['content'] ?? '',
            'updated_at'   =>  time(),
        ];
        if (!MemberPreferenceTypeRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setError('修改失败!');
            return false;
        }
        $this->setMessage('修改成功!');
        return true;
    }

    /**
     * 获取偏好类别信息
     * @param $request
     * @return bool
     */
    public function getPreferenceInfo($request)
    {
        if (!$res = MemberPreferenceTypeRepository::getOne(['id' => $request['id']])){
            $this->setError('类型不存在!');
            return false;
        }
        $this->setMessage('获取成功!');
        return $res;
    }
    /**
     * 获取偏好类别列表
     * @return bool
     */
    public function getPreferenceList()
    {
        if (!$list = MemberPreferenceTypeRepository::getList(['id' => ['<>',0]],['*'],'id','asc')){
            $this->setError('没有数据!');
            return false;
        }
        $type_id = array_column($list,'id');
        if (!$type_list = MemberPreferenceValueRepository::getList(['type_id' => ['in',$type_id]])){
            $this->setError('没有数据!');
            return false;
        }
        $value['next_level'] = [];
        foreach ($list as &$value){
            if (!$next_level = $this->searchArray($type_list,'type_id',$value['id'])) $next_level = [];
            $value['next_level'] = $next_level;
        }
        $this->setMessage('获取成功!');
        return $list;
    }

    /**
     * 添加成员偏好类别值属性
     * @param $request
     * @return bool
     */
    public function addPreferenceValue($request)
    {
        if (!MemberPreferenceTypeRepository::exists(['id' => $request['type']])){
            $this->setError('类别不存在!');
            return false;
        }
        if (MemberPreferenceValueRepository::exists(['name' => [$request['name']]])){
            $this->setError('类别值名称已存在!');
            return false;
        }
        $add_arr = [
            'name'      =>  $request['name'],
            'type_id'   =>  $request['type'],
            'content'   =>  $request['content'] ?? '',
            'create_at' =>  time(),
            'updated_at'=>  time(),
        ];
        if (!MemberPreferenceValueRepository::getAddId($add_arr)){
            $this->setError('添加失败!');
            return false;
        }
        $this->setMessage('添加成功!');
        return true;
    }

    /**
     * 删除偏好类别值属性（未使用则全部删除，使用中则不删除）
     * @param $request
     * @return bool
     */
    public function delPreferenceValue($request)
    {

        if (MemberPreferenceRepository::exists(['type' => [$request['id']]])){
            $this->setError('该类别值使用中,不能被删除!');
            return false;
        }
        DB::beginTransaction();
        if (MemberPreferenceRepository::exists(['type' => $request['id']])) if (!MemberPreferenceRepository::delete(['type' => $request['id']])){#业务表数据删除
            $this->setError('删除失败!');
            DB::rollBack();
            return false;
        }
        if (MemberPreferenceValueRepository::exists(['type_id' => $request['id']])) if (!MemberPreferenceValueRepository::delete(['type_id' => $request['id']])){#类型数据值表删除
            $this->setError('删除失败!');
            DB::rollBack();
            return false;
        }
        DB::commit();
        $this->setMessage('删除成功!');
        return true;
    }

    /**
     * 修改偏好类别属性
     * @param $request
     * @return bool
     */
    public function editPreferenceValue($request)
    {
        if (MemberPreferenceValueRepository::exists(array_merge(['id' => ['<>',$request['id']]],['name' => $request['name']]))){
            $this->setError('类别名称已存在!');
            return false;
        }
        $upd_arr = [
            'name'         =>  $request['name'],
            'content'      =>  $request['content'] ?? '',
            'updated_at'   =>  time(),
        ];
        if (!MemberPreferenceValueRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setError('修改失败!');
            return false;
        }
        $this->setMessage('修改成功!');
        return true;
    }
    /**
     * 偏好类别属性信息获取
     * @param $request
     * @return bool
     */
    public function getPreferenceValueInfo($request)
    {
        if (!$res = MemberPreferenceValueRepository::getOne(['id' => $request['id']])){
            $this->setError('类型属性不存在!');
            return false;
        }
        $this->setMessage('获取成功!');
        return $res;
    }

    /**
     * 偏好属性值获取列表
     * @param $request
     * @return mixed
     */
    public function getPreferenceValueList($request)
    {
        if (!MemberPreferenceTypeRepository::exists(['id' => $request['type']])){
            $this->setError('偏好类型不存在!');
            return false;
        }
        if (!$list = MemberPreferenceValueRepository::getList(['type_id' => $request['type']],['*'],'id','asc')){
            $this->setError('暂无数据');
            return [];
        }
        $this->setMessage('获取成功!');
        return $list;
    }
}
            