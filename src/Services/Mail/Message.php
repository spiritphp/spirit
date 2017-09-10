<?php

namespace Spirit\Services\Mail;

use Spirit\Engine;

class Message
{

    protected $subject;
    protected $message;

    /**
     * @var Person[]
     */
    protected $from = [];

    /**
     * @var Person[]
     */
    protected $to = [];
    protected $toOnlyMail = [];

    /**
     * @var Person[]
     */
    protected $cc = [];

    /**
     * @var Person[]
     */
    protected $bcc = [];

    /**
     * @var Person[]
     */
    protected $replyTo = [];

    /**
     * @var Property
     */
    protected $priority;

    /**
     * TODO
     * @var
     */
    protected $attach;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function subject($v)
    {
        $this->subject = $v;
        return $this;
    }

    public function from($mail, $name = null)
    {
        $this->from[] = new Person($mail, $name);

        return $this;
    }

    public function to($mail, $name = null)
    {
        $this->to[] = new Person($mail, $name);
        $this->toOnlyMail = $mail;

        return $this;
    }

    public function cc($mail, $name = null)
    {
        $this->cc[] = new Person($mail, $name);

        return $this;
    }

    public function bcc($mail, $name = null)
    {
        $this->cc[] = new Person($mail, $name);

        return $this;
    }

    public function replyTo($mail, $name = null)
    {
        $this->replyTo[] = new Person($mail, $name);

        return $this;
    }

    public function property($type)
    {
        $this->priority = new Property($type);

        return $this;
    }

    protected function headers()
    {
        $headers = [];

        $headers[] = "MIME-Version: 1.0";
        $headers[] = "X-Mailer: PHP/" . phpversion();
        $headers[] = "Message-ID: <" . time() . "-" . md5(implode(',', $this->from) . implode(',', $this->to)) . "@" . Engine::i()->domain . ">";
        $headers[] = "Date: " . date("r");
        $headers[] = "Content-type: text/html; charset=UTF-8";
        $headers[] = "Content-Transfer-Encoding: binary";
        $headers[] = "Subject: =?UTF-8?B?" . base64_encode($this->subject) . "?=";

        if ($this->priority) {
            $headers = array_merge($headers, $this->priority->get());
        }

        $headers[] = 'To: ' . implode(', ',$this->to);
        $headers[] = 'From: ' . implode(', ',$this->from);

        if (count($this->cc)) {
            $headers[] = 'Cc: ' . implode(', ',$this->cc);
        }

        if (count($this->bcc)) {
            $headers[] = 'Bcc: ' . implode(', ',$this->bcc);
        }

        if (count($this->replyTo)) {
            $headers[] = 'Reply-To: ' . implode(', ',$this->replyTo);
        }

        return implode("\r\n", $headers);
    }

    public function send()
    {
        if (count($this->from) == 0) {
            $this->from[] = new Person(Engine::cfg()->mail['from'], Engine::cfg()->mail['name']);
        }

        $subject = "=?UTF-8?B?" . base64_encode($this->subject) . "?=";

        if (Engine::cfg()->mail['type'] === 'log') {
            $this->log($this->headers(),$this->message);
        } else {
            mail(
                implode(', ',$this->toOnlyMail),
                $subject,
                $this->message,
                $this->headers()
            );
        }

    }

    protected function log($headers, $message)
    {
        $dir = 'emails/' . date('Y_m_d') . '/';

        $path = Engine::dir()->logs . $dir;

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        file_put_contents($path . date('Hi') . '.log', $headers . "\n" . $message . "\n\n" . str_repeat("=",30) . "\n", FILE_APPEND);
    }
}