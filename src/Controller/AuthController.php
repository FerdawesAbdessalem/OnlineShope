<?php

declare(strict_types=1);

namespace App\Controller;

use App\App;

final class AuthController
{
    public function __construct(private readonly App $app)
    {
    }

    public function loginForm(): string
    {
        if ($this->app->auth->check()) {
            redirect('/');
        }
        return $this->app->render('auth/login', ['title' => 'Connexion']);
    }

    public function login(): never
    {
        $this->app->verifyCsrf();
        $email = trim((string) ($_POST['email'] ?? ''));
        if (!$this->app->auth->attempt($email, (string) ($_POST['password'] ?? ''))) {
            $this->app->keepInput(['email' => $email]);
            flash('error', 'Adresse e-mail ou mot de passe incorrect.');
            redirect('/login');
        }
        $this->app->forgetInput();
        $intended = $_SESSION['intended'] ?? ($this->app->auth->isAdmin() ? '/admin' : '/');
        unset($_SESSION['intended']);
        flash('success', 'Bienvenue ' . $this->app->auth->user()['name'] . ' !');
        redirect(safe_path($intended));
    }

    public function registerForm(): string
    {
        return $this->app->render('auth/register', ['title' => 'Créer un compte', 'errors' => $this->takeErrors()]);
    }

    public function register(): never
    {
        $this->app->verifyCsrf();
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $errors = [];
        if (mb_strlen($name) < 2) {
            $errors['name'] = 'Indiquez votre nom.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse e-mail invalide.';
        } elseif ($this->app->users->findByEmail($email)) {
            $errors['email'] = 'Un compte existe déjà avec cette adresse.';
        }
        if (strlen($password) < 8) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
        } elseif ($password !== ($_POST['password_confirmation'] ?? '')) {
            $errors['password'] = 'Les mots de passe ne correspondent pas.';
        }

        if ($errors) {
            $_SESSION['errors'] = $errors;
            $this->app->keepInput($_POST);
            redirect('/register');
        }

        $this->app->auth->login($this->app->users->create($name, $email, $password));
        $this->app->forgetInput();
        flash('success', 'Votre compte a été créé. Bienvenue !');
        $intended = $_SESSION['intended'] ?? '/';
        unset($_SESSION['intended']);
        redirect(safe_path($intended));
    }

    public function logout(): never
    {
        $this->app->verifyCsrf();
        $this->app->auth->logout();
        flash('info', 'Vous êtes déconnecté.');
        redirect('/');
    }

    private function takeErrors(): array
    {
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);
        return $errors;
    }
}
