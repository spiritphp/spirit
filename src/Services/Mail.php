<?php

namespace Spirit\Services;

use Spirit\Services\Mail\Message;
use Spirit\Structure\Service;

class Mail extends Service {

    protected $directory = 'emails';

    public static function send($view, array $data, callable $callback)
    {
        static::make($callback)->message($view, $data)->send();
    }

    public static function sendRaw($text, callable $callback)
    {
        static::make($callback)->text($text)->send();
    }

    /**
     * @param $view
     * @param array $data
     * @return Message
     */
    public static function createMessage($view, array $data)
    {
        return static::make()->message($view,$data);
    }

    /**
     * @param $text
     * @return Message
     */
    public static function createMessageRaw($text)
    {
        return new Message($text);
    }

    public static function make(callable $callback = null)
    {
        return (new static($callback));
    }


    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var Message
     */
    protected $message;

    public function __construct(callable $callback = null)
    {
        $this->callback = $callback;
    }

    /**
     * @param null $view
     * @param null $data
     * @return Message
     */
    public function message($view = null, $data = null)
    {

        $this->message = new Message(parent::view($view,$data)->render());

        if ($this->callback) {
            call_user_func($this->callback, $this->message);
        }

        return $this->message;
    }

    /**
     * @param $text
     * @return Message
     */
    public function text($text)
    {
        $this->message = new Message($text);

        call_user_func($this->callback, $this->message);

        return $this->message;
    }
}