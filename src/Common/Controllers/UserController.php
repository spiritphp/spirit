<?php

namespace Spirit\Common\Controllers;

use Spirit\Common\Models\User\Info;
use Spirit\Services\Admin\CommonMenu as MenuAdmin;
use Spirit\Services\Form;
use Spirit\Services\Table;
use Spirit\Auth;
use Spirit\Request;
use Spirit\DB;
use Spirit\DB\Builder;
use Spirit\Structure\Controller;
use Spirit\Structure\Model;
use Spirit\Auth\U;
use Spirit\Common\Models\User as UserModel;

class UserController extends Controller
{

    use MenuAdmin;

    protected $directory = 'user/';

    protected $defaultConfig = [
        'menu' => [
            'user' => [
                'link' => 'user/{ID}',
                'title' => 'Пользователь'
            ],
            'info' => array(
                'link' => 'user/{ID}/info',
                'title' => 'Дополнительная информация'
            ),
            'apps' => array(
                'link' => 'user/{ID}/apps',
                'title' => 'Приложения пользователя'
            ),
            'role' => array(
                'link' => 'user/{ID}/role',
                'title' => 'Права пользователя'
            ),
        ],
        'accessDeniedText' => 'Доступ запрещён'
    ];

    // ********************************************
    // ********************************************
    // ********************************************

    //
    // ADMIN
    //

    protected function getArrMenu()
    {
        $menu = $this->c('menu');

        if (!U::acl(U::ROLE_USER_ACL)) {
            unset($menu['role']);
        }

        return $menu;
    }

    public function usersAdmin()
    {
        $items = UserModel::with('info')
            ->orderBy('id', 'desc');

        $data = [];

        if ($s = Request::get('search')) {
            $data['search'] = $s;

            $sstr = "%" . $s . "%";
            $items->where('id::varchar(255)', 'LIKE', $sstr)
                ->orWhere('email', 'LIKE', $sstr)
                ->orWhere('login', 'LIKE', $sstr);
        }

        $items = $items->paginate();

        $table = Table::make($items)
            ->addColumn('id', 'ID')
            ->addColumn('login', 'Логин', function (UserModel $user) {
                if ($user->login) {
                    return $user->login;
                } else {
                    return $user->info->first_name . ' ' . $user->info->last_name;
                }
            })
            ->setLink($this->urlA('user/{ID}'))
            ->addColumn('email', 'Электронная почта')
            ->draw();

        $data['table'] = $table;

        return $this->adminview('{__SPIRIT__}/user/admin/users.php', $data);
    }

    public function userAdmin($user_id)
    {
        $user = DB::table('users')->where('id', $user_id)->first();

        if (!$user) {
            $this->abort(404);
        }

        $form = Form::make()
            ->text('email', 'Электронная почта', 'email|unique:users,email,' . $user_id)
            ->text('login', 'Логин', 'required|unique:users,login,' . $user_id)
            ->text('date', 'Дата регистрации', 'required')
            ->text('ip', 'IP')
            ->checkbox('active', 'Пользователь активен')
            ->checkbox('online', 'Онлайн')
            ->text('date_online', 'Дата онлайна')
            ->text('version', 'Версия куки', 'required')
            ->text('block', 'Блокировать сообщением')
            ->submit('Сохранить', 'btn btn-success')
            ->withDefaultData($user);

        $success = false;
        if ($form->check()) {
            DB::table('users')->where('id', $user_id)->update($form->getData());
            $success = true;
        }

        $data = [
            'form' => $form->draw(),
            'success' => $success,
            'menu' => $this->menuAdmin($this->getArrMenu(), 'user', $user_id)
        ];

        return $this->adminview('{__SPIRIT__}/user/admin/user.php', $data);
    }

    public function userInfoAdmin($user_id)
    {
        $user = Info::find($user_id);

        if (!$user) {
            $this->abort(404);
        }

        $form = Form::make()
            ->text('first_name', 'Имя')
            ->text('last_name', 'Фамилия')
            ->date('birthday', 'Дата рождения')
            ->select('gender', [0 => 'Не указан', 1 => 'Мужской', 2 => 'Женский'], 'Пол')
            ->submit('Сохранить', 'btn btn-success')
            ->withDefaultData($user->info);

        $success = false;
        if ($form->check()) {
            $user->info = $form->getData();
            $user->save();
            $success = true;
        }

        $data = [
            'form' => $form->draw(),
            'success' => $success,
            'menu' => $this->menuAdmin($this->getArrMenu(), 'info', $user_id)
        ];

        return $this->adminview('{__SPIRIT__}/user/admin/user.php', $data);
    }

    public function userRoleAdmin($user_id)
    {
        $user = UserModel::find($user_id);

        if (!$user) {
            $this->abort(404);
        }

        $roleDescriptions = \App\U::$roleDescriptions;

        $roles = [];
        foreach ($user->roles as $role_name) {
            $roles[$role_name] = true;
        }

        $form = Form::make();
        foreach ($roleDescriptions as $role_name => $role_description) {
            $form->checkbox($role_name, $role_description);
        }

        $form
            ->submit('Сохранить', 'btn btn-success')
            ->withDefaultData($roles);


        $success = false;
        if ($form->check()) {

            $d = $form->getData();

            $saveRoles = [];
            foreach ($d as $k => $v) {
                if ($v) {
                    $saveRoles[] = $k;
                }
            }

            $user->roles = $saveRoles;
            $user->save();

            $success = true;
        }

        $data = [
            'form' => $form->draw(),
            'success' => $success,
            'menu' => $this->menuAdmin($this->getArrMenu(), 'role', $user_id)
        ];

        return $this->adminview('{__SPIRIT__}/user/admin/user.php', $data);
    }

    public function userAppsAdmin($user_id)
    {
        if (!$user = UserModel::find($user_id)) {
            $this->abort(404);
        }

        $table = Table::make($user->apps)
            ->addColumn('alias', 'Приложение')
            ->addColumn('app_user_id', 'ID в приложении')
            ->addColumn('token', 'Токен')
            ->addColumn('date', 'Дата')
            ->draw();

        $data = [
            'table' => $table,
            'menu' => $this->menuAdmin($this->getArrMenu(), 'apps', $user_id)
        ];

        return $this->adminview('{__SPIRIT__}/user/admin/apps.php', $data);
    }
}
