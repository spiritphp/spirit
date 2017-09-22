<?php

namespace Spirit\Common\Models\User;

use Spirit\Engine;
use Spirit\Structure\Model;
use Spirit\Common\Models\User;

/**
 * Class Log
 * @package Spirit\Common\Models\User
 *
 * @property integer $user_id
 * @property string $hash
 * @property string $ip
 * @property string $user_agent
 *
 * @property User $user
 *
 * @property string $version
 */
class Log extends Model
{
    protected $timestamps = true;
    protected $table = 'user_logs';

    public function user()
    {
        return $this->belongTo(Engine::cfg()->userModel, 'id', 'user_id');
    }

    public function getDateData($v)
    {
        return date("d.m.Y H:i:s", strtotime($v));
    }
}