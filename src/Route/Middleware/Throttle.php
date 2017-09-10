<?php

namespace Spirit\Route\Middleware;

use Spirit\Engine;
use Spirit\Error;
use Spirit\Structure\Middleware;
use Spirit\Request\Throttle as ThrottleService;

class Throttle extends Middleware
{
    /**
     * @var ThrottleService
     */
    protected $throttleInstance;
    protected $result;

    public function handle($var = null)
    {
        $amount = 60;
        $timeout = 1 * 60;

        if ($var) {
            $varArr = explode(',', $var, 2);

            $amount = $varArr[0];

            if (isset($varArr[1])) {
                $timeout = $varArr[1];
            }
        }

        $this->throttleInstance = ThrottleService::make($amount, $timeout);

        if (!$this->throttleInstance->check()) {
            throw new Error\HttpException(429,null,$this->headers(false));
        }

        Engine::i()->constructor()->headers($this->headers());

        return true;
    }

    protected function headers($success = true)
    {
        $headers = [
            'X-RateLimit-Limit' => $this->throttleInstance->limit(),
            'X-RateLimit-Remaining' => $this->throttleInstance->remaining()
        ];

        if(!$success) {
            $headers['Retry-After'] = $this->throttleInstance->retryAfter();
            $headers['X-RateLimit-Reset'] = $this->throttleInstance->rateLimitReset();
        }

        return $headers;

    }
}