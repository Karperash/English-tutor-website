<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;

final class AuthController extends Controller
{
    public function form(): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            $this->redirect(($user['role'] ?? '') === 'teacher' ? '/teacher' : '/student');
        }
        $this->view('auth/login', [], 'public');
    }

    public function login(): void
    {
        Csrf::guard();
        $login = $this->input('login');
        $password = (string)($_POST['password'] ?? '');

        if ($login === '' || $password === '') {
            Flash::set('error', 'Введите логин и пароль.');
            $this->redirect('/login');
        }

        if (!Auth::attempt($login, $password)) {
            Flash::set('error', 'Неверный логин или пароль.');
            $this->redirect('/login');
        }

        $user = Auth::user();
        Flash::set('success', 'Вы успешно вошли в аккаунт.');
        $this->redirect(($user['role'] ?? '') === 'teacher' ? '/teacher' : '/student');
    }

    public function logout(): void
    {
        Csrf::guard();
        Auth::logout();
        Flash::set('success', 'Вы вышли из аккаунта.');
        $this->redirect('/');
    }
}
