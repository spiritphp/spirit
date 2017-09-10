<?php

namespace Spirit\Services\Mail;

class Person
{

    /**
     * @var string
     */
    protected $mail;

    /**
     * @var string|null
     */
    protected $name;

    public function __construct($mail, $name = null)
    {
        $this->mail = $mail;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function toString()
    {
        if ($this->name) {
            return "=?UTF-8?B?" . base64_encode($this->name) . "?= <" . $this->mail . ">";
        }

        return $this->mail;
    }

    public function __toString()
    {
        return $this->toString();
    }
}