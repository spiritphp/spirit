<?php

namespace Spirit\Common\Models\User;

use Spirit\Engine;
use Spirit\Structure\Model;

/**
 * Class Recovery
 * @package Spirit\Common\Models\User
 *
 * @property string $fix
 * @property string $token
 * @property integer $user_id
 * @property string $ip
 * @property string $ip_used
 * @property string $used_at
 */
class Recovery extends Model {

    protected $table = 'user_recoveries';

    public function user()
    {
        return $this->belongTo(Engine::cfg()->userModel, 'id', 'user_id');
    }

}