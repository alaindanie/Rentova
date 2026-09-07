<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Router;
use App\Core\Validator;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\Utilisateur;

/**
 * Authentification : connexion, inscription, déconnexion.
 */
class AuthController extends Controller
{
    protected string $layout = 'auth';

    public function loginForm(): void
    {
        if (Auth::check()) {
            Router::redirect($this->home());
        }
        $this->view('auth/login');
    }

    public function login(): void
    {
        $this->verifyCsrf();

        $now = time();
        $attempts = array_values(array_filter(
            Session::get('login_attempts', []),
            static fn (int $t): bool => $t > $now - 900
        ));
        if (count($attempts) >= 5) {
            $this->flash('error', 'Trop de tentatives de connexion. Réessayez dans quelques minutes.');
            Router::redirect('login');
        }

        $validator = new Validator($_POST);
        $validator->validate([
            'email'    => ['E-mail', 'required|email|max:150'],
            'password' => ['Mot de passe', 'required|min:6'],
        ]);

        if ($validator->fails()) {
            $this->flash('error', $validator->firstError());
            Session::set('old_email', $_POST['email'] ?? '');
            Router::redirect('login');
        }

        $user = Utilisateur::attempt($_POST['email'], $_POST['password']);
        if (!$user) {
            $attempts[] = $now;
            Session::set('login_attempts', $attempts);
            $this->flash('error', 'E-mail ou mot de passe incorrect.');
            Session::set('old_email', $_POST['email']);
            Router::redirect('login');
        }

        Session::remove('login_attempts');
        Auth::login($user);
        Session::set('confetti', true);
        $this->flash('success', 'Bienvenue ' . Auth::name() . ' !');
        Router::redirect($this->home());
    }

    public function registerForm(): void
    {
        if (Auth::check()) {
            Router::redirect($this->home());
        }
        $this->view('auth/register');
    }

    public function register(): void
    {
        $this->verifyCsrf();
        $validator = new Validator($_POST);
        $validator->validate([
            'prenom'    => ['Prénom', 'required|min:2|max:100'],
            'nom'       => ['Nom', 'required|min:2|max:100'],
            'email'     => ['E-mail', 'required|email|max:150'],
            'telephone' => ['Téléphone', 'max:30'],
            'adresse'   => ['Adresse', 'max:255'],
            'password'  => ['Mot de passe', 'required|min:6|max:100'],
            'confirm'   => ['Confirmation', 'required|same:password'],
            'cgv'       => ['Acceptation des conditions d\'utilisation', 'required'],
        ]);

        if ($validator->fails()) {
            $this->flash('error', $validator->firstError());
            Session::set('old', $_POST);
            Router::redirect('register');
        }

        if (Utilisateur::findByEmail($_POST['email'])) {
            $this->flash('error', 'Un compte existe déjà avec cet e-mail.');
            Session::set('old', $_POST);
            Router::redirect('register');
        }

        $id = Utilisateur::create([
            'nom'       => $_POST['nom'],
            'prenom'    => $_POST['prenom'],
            'email'     => $_POST['email'],
            'telephone' => $_POST['telephone'] ?? null,
            'adresse'   => $_POST['adresse'] ?? null,
            'password'  => $_POST['password'],
            'role'      => 'client',
        ]);

        $user = Utilisateur::find($id);
        unset($user['password']);
        Auth::login($user);
        Session::set('confetti', true);
        $this->flash('success', 'Votre compte a été créé avec succès. Bienvenue !');
        Router::redirect('dashboard');
    }

    public function logout(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            Router::redirect('');
        }
        $this->verifyCsrf();
        Auth::logout();
        Router::redirect('');
    }

    /** Page d'accueil selon le rôle. */
    private function home(): string
    {
        return match (Auth::role()) {
            'responsable' => 'dashboard',
            'agent'       => 'locations',
            default       => 'catalogue',
        };
    }
}
