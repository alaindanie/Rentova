<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Router;
use App\Core\Validator;
use App\Core\Auth;
use App\Core\Upload;
use App\Models\Utilisateur;

/**
 * Gestion des utilisateurs (CRUD) — Responsable, et profil personnel.
 */
class UtilisateurController extends Controller
{
    protected string $layout = 'dashboard';

    public function index(): void
    {
        $this->requireRole('responsable');
        $this->view('utilisateurs/index', ['utilisateurs' => Utilisateur::all()]);
    }

    public function create(): void
    {
        $this->requireRole('responsable');
        $this->view('utilisateurs/form', ['utilisateur' => null, 'title' => 'Nouvel utilisateur']);
    }

    public function store(): void
    {
        $this->requireRole('responsable');
        $this->verifyCsrf();

        $errors = $this->validateUser($_POST);
        if ($errors) {
            $this->flash('error', $errors);
            Router::redirect('utilisateurs/creer');
        }
        if (Utilisateur::findByEmail($_POST['email'])) {
            $this->flash('error', 'Cet e-mail est déjà utilisé.');
            Router::redirect('utilisateurs/creer');
        }

        Utilisateur::create([
            'nom'       => $_POST['nom'],
            'prenom'    => $_POST['prenom'],
            'email'     => $_POST['email'],
            'telephone' => $_POST['telephone'] ?? null,
            'adresse'   => $_POST['adresse'] ?? null,
            'password'  => $_POST['password'],
            'role'      => $_POST['role'],
            'photo'     => $this->handlePhotoUpload(),
        ]);
        $this->flash('success', 'L\'utilisateur a été créé.');
        Router::redirect('utilisateurs');
    }

    /** Upload la photo de profil si un fichier est envoyé, sinon retourne null. */
    private function handlePhotoUpload(): ?string
    {
        if (empty($_FILES['photo']['name'])) {
            return null;
        }
        try {
            return Upload::image($_FILES['photo'], 'profils');
        } catch (\RuntimeException $e) {
            $this->flash('error', $e->getMessage());
            return null;
        }
    }

    public function edit(string $id): void
    {
        $this->requireRole('responsable');
        $utilisateur = Utilisateur::find((int) $id);
        if (!$utilisateur) {
            (new ErrorController())->notFound();
            return;
        }
        $this->view('utilisateurs/form', [
            'utilisateur' => $utilisateur,
            'title'       => "Modifier : {$utilisateur['prenom']} {$utilisateur['nom']}",
        ]);
    }

    public function update(string $id): void
    {
        $this->requireRole('responsable');
        $this->verifyCsrf();

        $utilisateur = Utilisateur::find((int) $id);
        if (!$utilisateur) {
            (new ErrorController())->notFound();
            return;
        }

        $validator = new Validator($_POST);
        $validator->validate([
            'nom'       => ['Nom', 'required|min:2|max:100'],
            'prenom'    => ['Prénom', 'required|min:2|max:100'],
            'email'     => ['E-mail', 'required|email|max:150'],
            'password'  => ['Mot de passe', 'max:100|min:6'],
            'role'      => ['Rôle', 'required|in:client,agent,responsable'],
        ]);
        if ($validator->fails()) {
            $this->flash('error', $validator->firstError());
            Router::redirect('utilisateurs/modifier/' . $id);
        }

        $existing = Utilisateur::findByEmail($_POST['email']);
        if ($existing && (int) $existing['id'] !== (int) $id) {
            $this->flash('error', 'Cet e-mail est déjà utilisé par un autre compte.');
            Router::redirect('utilisateurs/modifier/' . $id);
        }

        $data = $_POST;
        $photo = $this->handlePhotoUpload();
        if ($photo) {
            Upload::remove($utilisateur['photo'] ?? null, 'profils');
            $data['photo'] = $photo;
        }
        Utilisateur::update((int) $id, $data);
        $this->flash('success', 'L\'utilisateur a été mis à jour.');
        Router::redirect('utilisateurs');
    }

    public function delete(string $id): void
    {
        $this->requireRole('responsable');
        $this->verifyCsrf();

        $utilisateur = Utilisateur::find((int) $id);
        if (!$utilisateur) {
            $this->flash('error', 'Utilisateur introuvable.');
        } elseif ((int) $id === Auth::id()) {
            $this->flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        } else {
            Utilisateur::delete((int) $id);
            $this->flash('success', 'L\'utilisateur a été supprimé.');
        }
        Router::redirect('utilisateurs');
    }

    /* --------------------------- Profil personnel --------------------------- */

    public function profile(): void
    {
        $this->requireLogin();
        $this->view('utilisateurs/profile', ['utilisateur' => Auth::user()]);
    }

    public function updateProfile(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();

        $id = Auth::id();
        $validator = new Validator($_POST);
        $validator->validate([
            'nom'       => ['Nom', 'required|min:2|max:100'],
            'prenom'    => ['Prénom', 'required|min:2|max:100'],
            'email'     => ['E-mail', 'required|email|max:150'],
            'telephone' => ['Téléphone', 'max:30'],
            'adresse'   => ['Adresse', 'max:255'],
        ]);
        if (!empty($_POST['password'])) {
            $validator->validate(['password' => ['Mot de passe', 'min:6|max:100']]);
        }
        if ($validator->fails()) {
            $this->flash('error', $validator->firstError());
            Router::redirect('profil');
        }

        $existing = Utilisateur::findByEmail($_POST['email']);
        if ($existing && (int) $existing['id'] !== $id) {
            $this->flash('error', 'Cet e-mail est déjà utilisé.');
            Router::redirect('profil');
        }

        $data = $_POST;
        $data['role'] = Auth::role();
        $photo = $this->handlePhotoUpload();
        if ($photo) {
            Upload::remove(Auth::user()['photo'] ?? null, 'profils');
            $data['photo'] = $photo;
        }
        Utilisateur::update($id, $data);
        Auth::refresh();
        $this->flash('success', 'Votre profil a été mis à jour.');
        Router::redirect('profil');
    }

    /* --------------------------- Helpers --------------------------- */

    private function validateUser(array $d): ?string
    {
        $validator = new Validator($d);
        $validator->validate([
            'nom'       => ['Nom', 'required|min:2|max:100'],
            'prenom'    => ['Prénom', 'required|min:2|max:100'],
            'email'     => ['E-mail', 'required|email|max:150'],
            'telephone' => ['Téléphone', 'max:30'],
            'password'  => ['Mot de passe', 'required|min:6|max:100'],
            'role'      => ['Rôle', 'required|in:client,agent,responsable'],
        ]);
        return $validator->fails() ? $validator->firstError() : null;
    }
}
