<?php

namespace Spirit\Common\Controllers\Api;

use App\Models\User;
use Spirit\Engine;
use Spirit\Services\VarCheck;
use Spirit\Auth as AuthSrc;
use Spirit\Auth\App;
use Spirit\Auth\Login;
use Spirit\Auth\Recovery;
use Spirit\Auth\Registration;
use Spirit\Request\Client;
use Spirit\Request;

class Auth extends Abc
{

    protected function viewError($error, $error_vars = [])
    {
        $e = [
            'error' => $error
        ];

        if ($error_vars && is_array($error_vars) && count($error_vars)) {
            $e['error_vars'] = $error_vars;
        }

        return $e;
    }

    public function token()
    {
        if (!$token = Request::get('token')) {
            return false;
        }

        if (!$user = $this->getUserForToken($token)) {
            return $this->error(Abc::ERROR_TOKEN);
        }

        if ($user->block) {
            return $this->error(Abc::ERROR_USER_BLOCK);
        }

        return $this->getUser($user);
    }

    public function tokenTrust()
    {
        if (!$this->isTrust()) return false;

        if (!$token = Request::get('token')) {
            return false;
        }

        if (!$user = $this->getUserForToken($token)) {
            return $this->error(Abc::ERROR_TOKEN);
        }

        return $this->getUser($user, ['block', 'id']);
    }

    public function login()
    {
        usleep(mt_rand(100, 500));

        $login = Request::get('login');
        $password = Request::get('password');

        if (!$login || !$password) {
            return $this->viewError('error.auth');
        }

        $ipCheck = $this->ipCheck();

        if (is_string($ipCheck)) {
            return $this->viewError('error.' . $ipCheck);
        }

        $user = Login::make()
            ->setPassword($password)
            ->setLogin($login)
            ->getUser();

        $error_description = null;
        if (!$user) {
            $error = 'error.auth';
        } elseif (!$user->active) {
            $error = 'error.activation';
        } elseif ($user->block) {
            $error = 'error.block';
            $error_description = ['block' => $user->block];
        } else {
            return $this->getUser($user);
        }

        $ipCheck->log();

        return $this->viewError($error, $error_description);
    }

    public function registration()
    {
        usleep(mt_rand(100, 500));

        $login = Request::get('login');
        $password = Request::get('password');
        $email = Request::get('email');

        $error = false;

        $ipReqCheck = VarCheck::make(Client::getIP(), 'registration');
        $ipCheck = $this->ipCheck();

        if (is_string($ipCheck)) {
            return $this->viewError('error.' . $ipCheck);
        }

        if (!$ipReqCheck->checkAmountLimit(3)) {
            return $this->viewError('error.lock');
        }

        if ($wait_time = $ipReqCheck->getTimeWait(60)) {
            return $this->viewError('error.wait_time', ['sec' => $wait_time]);
        }

        if (!$login || !$password) {
            $error = 'error.empty';
        } elseif ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'error.email';
        } elseif ($login && !preg_match("/^[a-zа-яё0-9\-_ ]{1,32}$/ius", $login)) {
            $error = 'error.login';
        } elseif (User::where('login', $login)->first()) {
            $error = 'error.login_use';
            $ipCheck->log();
        } elseif ($email && User::where('email', $email)->first()) {
            $error = 'error.email_use';
            $ipCheck->log();
        }

        if ($error) {
            return $this->viewError($error);
        }

        $ipReqCheck->log();
        $registration = Registration::make()
            ->setLogin($login)
            ->setPassword($password);

        if ($email) {
            $registration->setEmail($email);
        }

        $user = $registration->create();

        return $this->getUser($user);
    }

    public function recovery()
    {
        usleep(mt_rand(100, 500));

        $email = Request::get('email');

        if (!$email) {
            return $this->viewError('error.empty');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->viewError('error.email');
        }

        $ipCheck = VarCheck::make(Client::getIP(), 'recovery');
        if (!$ipCheck->checkAmountLimit(7)) {
            return $this->viewError('error.lock');
        }

        if (!$ipCheck->checkTimeDelay(2)) {
            return $this->viewError('error.time');
        }

        $loginCheck = VarCheck::make($email, 'recovery_email');
        if (!$loginCheck->checkAmountLimit(5)) {
            return $this->viewError('error.lock_email');
        }

        if ($wait_time = $loginCheck->getTimeWait(300)) {
            return $this->viewError('error.wait_time', ['sec' => $wait_time]);
        }


        $result = Recovery::make()
            ->setEmail($email)
            ->send(
                'Восстановление пароля',
                Engine::dir()->views . 'email/recovery.php'
            );

        $ipCheck->log();

        if (!$result) {
            return $this->viewError('error.empty');
        }

        $loginCheck->log();

        return [
            'success' => 1
        ];
    }

    public function vk()
    {
        $user = App::make('vk')->appUser();

        return $this->getUser($user);
    }
}