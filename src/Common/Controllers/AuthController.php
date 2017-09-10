<?php

namespace Spirit\Common\Controllers;

use Spirit\Auth;
use Spirit\Common\Controllers\Auth\App;
use Spirit\Auth\Services\Login;
use Spirit\Auth\Services\Recovery;
use Spirit\Auth\Services\Registration;
use Spirit\Auth\Services\Activation;
use Spirit\Engine;
use Spirit\Response\Captcha;
use Spirit\Services\Form;
use Spirit\Response\Redirect;
use Spirit\Structure\Controller;

class AuthController extends Controller
{
    function captcha($uniqid)
    {
        $this->isOnlyThis();

        $result = Captcha::make()
            ->uniqueId($uniqid)
            ->draw();

        if (!$result) {
            $this->abort(404);
        }

        return '';
    }

    public function logout()
    {
        if (Auth::guest()) {
            $this->abort(404);
        }

        Auth::logout();
    }

    public function login()
    {
        $form = Form::make()
            ->text('login', 'Никнейм', 'required')
            ->password('password', 'Пароль', 'required')
            ->protectCaptcha(3)
            ->submit('Вход', 'btn btn-success');

        $form->setError(
            [
                'login' => [
                    'required' => 'Введите никнейм',
                ],
                'password' => [
                    'required' => 'Вы не ввели пароль',
                ],
            ]
        );

        if ($form->check()) {

            $d = $form->getData();

            $user = Login::make()
                ->setPassword($d['password'])
                ->setLogin($d['login'])
                ->getUser();

            if ($user) {

                if (!$user->active) {
                    // Аккаунт не активен
                    $form->addError(
                        'Аккаунт ещё не активен.' .
                        'Мы повторно отправили письмо с активацией на вашу электронную почту.' .
                        'Перейдите по ссылке в письме для активации вашего аккаунта'
                    );

                    Activation::make()
                        ->setUserID($user->id)
                        ->setLogin($user->login)
                        ->setEmail($user->email)
                        ->setUID($user->uid)
                        ->send('Активация', Engine::dir()->views . 'auth/email/activation.php');

                } elseif ($user->block) {
                    $form->addError('Аккаунт заблокирован.<br/>Причина: ' . $user->block);
                } else {
                    Auth::setUserCookie($user->id);
                    return Redirect::home();
                }
            } else {
                $form->addError('Неверный логин или пароль');
            }
        }

        return $this->view('auth/login',[
            'form' => $form
        ]);
    }

    public function registration()
    {
        $form = Form::make()
            ->text('login', 'Никнейм', 'required|unique:users,login')
            ->text('email', 'Электронная почта', 'email|unique:users,email')
            ->password('password', 'Пароль', 'required')
            ->protectCaptcha(1)
            ->submit('Регистрация');

        $form->setError(
            [
                'login' => [
                    'required' => 'Введите никнейм',
                    'unique' => 'Указанный никнейм занят'
                ],
                'email' => [
                    'required' => 'Чтобы зарегистрироваться, нужно ввести электронную почту',
                    'email' => 'Вы указали неверную электронную почту',
                    'unique' => 'Указанная электронная почта уже зарегистрирована'
                ],
                'password' => [
                    'required' => 'Вы не ввели пароль',
                ],
            ]
        );

        if ($form->check()) {
            $d = $form->getData();
            $user = Registration::make()
                ->setLogin($d['login'])
                ->setEmail($d['email'])
                ->setPassword($d['password'])
                ->withActivation()
                ->sendActivation('Активация', Engine::dir()->views . 'auth/email/activation.php')
                ->sendWelcome('Регистрация', Engine::dir()->views . 'auth/email/registration.php')
                ->create();

            Auth::setUserCookie($user->id);
            return Redirect::home();
        }

        return $this->view('auth/registration',[
            'form' => $form
        ]);
    }

    public function activation($code)
    {
        if ($user = Activation::make()->setCode($code)->activate()) {
            Auth::setUserCookie($user->id);
            return Redirect::home();
        }

        return Redirect::to('login');
    }

    public function recovery($hash = false)
    {
        if ($hash) {
            $r = Recovery::make()->setHash($hash);

            if ($r->check()) {
                $form = Form::make()
                    ->text('password', 'Новый пароль', 'required|confirmed')
                    ->text('password_confirmation', 'Повторите пароль', 'required')
                    ->submit('Сменить пароль');

                $form->setError(
                    [
                        'password' => [
                            'required' => 'Введите пароль',
                        ]
                    ]
                );

                if ($form->check()) {

                    if ($r->setNewPassword($form->get('password'))) {
                        $user_id = $r->getUserID();
                        Auth::setUserCookie($user_id);
                        return Redirect::home();
                    }

                }

                $data = [
                    'form' => $form
                ];

                return $this->view('recoverysetpassword', $data);
            }
        }

        $form = Form::make()
            ->text('login', 'Логин или электронная почта', 'required')
            //->protectCaptcha(0)
            ->submit('Восстановить');

        $form->setError(
            [
                'login' => [
                    'required' => 'Введите логин или электронную почту',
                ]
            ]
        );

        if ($form->check()) {

            $result = Recovery::make()
                ->setLogin($form->get('login'))
                ->send(
                    'Восстановление пароля',
                    Engine::dir()->views . 'auth/email/recovery.php'
                );

            if (!$result) {
                $form->addError('Вы указали неверные данные');
            }
        }

        $data = [
            'form' => $form
        ];
        return $this->view('auth/recovery', $data);
    }

    public function auth($appName = false)
    {
        $data = [];

        $cfg = $this->cfg();

        $app = App::make($cfg['app']);

        if ($user = $app->auth($appName)) {
            Auth::setUserCookie($user->id);
            return Redirect::home();
        }

        $list = App::make($cfg['app'])->getAppsInfo();

        $data['list'] = $list;

        return $this->view('auth/auth',$data);
    }

}