<?php

namespace Spirit\DB;

use Spirit\DB;

class PostgreSQL extends Connection
{
    const TYPE_BOUNCER = 'bouncer';
    const TYPE_POOL = 'pool';
    const TYPE_DEFAULT = 'default';

    protected $driver = DB::DRIVER_POSTGRESQL;


}