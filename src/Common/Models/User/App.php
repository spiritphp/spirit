<?php

namespace Spirit\Common\Models\User;

use Spirit\Structure\Model;
use Spirit\Common\Models\User;

/**
 * Class App
 * @package Spirit\Common\Models\User
 *
 * @property integer $user_id
 * @property string $app_user_id
 * @property string $alias
 * @property string $token
 * @property string $hash
 * @property string $date
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $user
 *
 * @property string $version
 */
class App extends Model
{
    protected $timestamps = true;
    protected $table = 'user_app';
    protected $primaryKey = 'user_id';

    public function user()
    {
        return $this->belongTo(User::class, 'id', 'user_id');
    }

    public function getDateData()
    {
        return date("d.m.Y H:i:s", strtotime($this->created_at));
    }
}