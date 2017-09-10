<?php

namespace Spirit\Common\Models\User;

use Spirit\Structure\Model;
use Spirit\Common\Models\User;

/**
 * Class UserInfo
 * @package Spirit\Common\Models
 *
 * @property array|string $info
 * @property string $first_name
 * @property string $last_name
 *
 * @property User $user
 */
class Info extends Model
{

    protected $table = 'user_info';
    protected $primaryKey = 'user_id';
    protected $mutatorJson = ['info'];

    public function user()
    {
        return $this->belongTo(User::class, 'id', 'user_id');
    }

    protected function getInfoData($key)
    {
        if (isset($this->info[$key]) && $this->info[$key]) {
            return $this->info[$key];
        }

        return null;
    }

    protected function setInfoData($key, $value)
    {
        $this->info[$key] = $value;
    }

    public function getFirstNameData()
    {
        return $this->getInfoData('first_name');
    }

    public function getLastNameData()
    {
        return $this->getInfoData('last_name');
    }
}