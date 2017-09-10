<?php

namespace Spirit\Console\Commands;

use Spirit\Console;
use Spirit\Services\Mail;
use Spirit\Structure\Command;

class CheckMail extends Command
{
    protected $description = 'Check a server mail';
    protected $descriptionCommands = [
        'send' => [
            'title' => 'Send an email',
            'example' => 'php spirit check_mail user@mail.com',
            'params' => [
                'user@mail.com' => 'email for testing'
            ]
        ],
    ];

    protected function command()
    {
        $to = $this->getFirstBoolArg();

        if (!$to) {
            echo Console::textStyle('Params «to» is not found', 'black', 'red') . "\n";
            return;
        }

        Mail::sendRaw('test huest',function(Mail\Message $message) use($to) {
            $message->to($to)
                ->subject('Test Spirit Mail')
            ;
        });
        echo Console::textStyle('Message has sent', 'black', 'green') . "\n";
    }

}