<?php

namespace Spirit\Services\Mail;

class Property {

    const TYPE_HIGH = 'high';
    const TYPE_NORMAL = 'normal';
    const TYPE_LOW = 'low';

    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function get()
    {
        if ($this->type === static::TYPE_HIGH) {
            return [
                "X-Priority: 1 (Highest)",
                "X-MSMail-Priority: High",
                "Importance: High",
            ];
        } else if($this->type === static::TYPE_LOW) {
            return [
                "X-Priority: 5 (Lowest)",
                "X-MSMail-Priority: Low",
                "Importance: Low",
            ];
        } else {
            return [];
        }
    }

}