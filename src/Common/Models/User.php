<?php

namespace Spirit\Common\Models;

use Spirit\DB\Builder;
use Spirit\Structure\Model;

/**
 * Class User
 * @package Spirit\Common\Models
 *
 * @property string $uid
 * @property string $token
 * @property string $email
 * @property string $password
 * @property string|array $roles
 * @property string $version
 * @property boolean $online
 * @property string $date_online
 *
 * @property string $fix
 */
class User extends Model
{
    use Model\SoftRemoveTrait;

    const ONLINE_MINUTE = 15;

    protected $timestamps = true;

    protected $table = 'users';

    protected $rules = [
        'email' => 'required|email'
    ];

    protected $title = [
        'email' => 'Электронная почта'
    ];

    protected $mutatorJson = ['roles'];
    protected $mutatorBoolean = ['active'];

    public function getOnlineData()
    {
        return (time() - strtotime($this->date_online)) < static::ONLINE_MINUTE * 60;
    }

    /**
     * @return \Spirit\Structure\Model\Relations\HasMany|Builder
     */
    public function logs()
    {
        return $this->hasMany(User\Log::class, 'user_id', 'id');
    }

    public function acl($role, $usingRoot = true)
    {
        $roles = $this->roles;

        if ($usingRoot && in_array('root', $roles)) {
            return true;
        }

        return in_array($role, $roles);
    }
}