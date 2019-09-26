<?php


namespace App\Repositories;


use App\Models\MemberBindModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberBindRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberBindModel $model)
    {
        $this->model = $model;
    }

    /**
     * 新增微信用户
     * @param string $identifier openid
     * @param $access_token
     * @param string $additional
     * @param string $user_id
     * @param string $identity_type
     * @return bool|null
     */
    protected function createWeChatUser(string $identifier, $access_token, string $additional = '',string $user_id = '0', string $identity_type = '1')
    {
        if($this->exists(['identifier' => $identifier])){
            return true;
        }
        return $this->getAddId([
            'user_id' => $user_id,
            'identifier' => $identifier,
            'identity_type' => $identity_type,
            'credential' => $access_token,
            'last_login' => time(),
            'ip_address' => request()->ip(),
            'additional' => $additional,
            'created_at' => time(),
        ]);
    }
}
            