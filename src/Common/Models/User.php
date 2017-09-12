<?php

namespace Spirit\Common\Models;

use Spirit\DB\Builder;
use Spirit\Structure\Model;

/**
 * Class User
 * @package Spirit\Common\Models
 *
 * @property string $uid
 * @property string $login
 * @property string $email
 * @property string $token
 * @property string $password
 * @property string|array $roles
 * @property string $ip
 * @property string $date
 * @property string $version
 * @property string $block
 * @property boolean $active
 * @property boolean $online
 * @property string $date_online
 *
 * @property user\Info $info
 * @property user\App[] $apps
 *
 * @property string $fix
 */
class User extends Model
{
    const ONLINE_MINUTE = 15;

    protected $timestamps = true;

    protected $table = 'users';

    protected $fillable = [
        '*'
    ];

    protected $protect = [
        //'active'
    ];

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

    public function info()
    {
        return $this->hasOne(User\Info::class, 'user_id', 'id');
    }

    /**
     * @return \Spirit\Structure\Model\Relations\HasMany|Builder
     */
    public function logs()
    {
        return $this->hasMany(User\Log::class, 'user_id', 'id');
    }

    public function apps()
    {
        return $this->hasMany(User\App::class, 'user_id', 'id');
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