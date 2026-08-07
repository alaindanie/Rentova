<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Router;
use App\Core\Validator;
use App\Core\Upload;
use App\Models\Categorie;
use App\Models\Equipement;

/**
 * Gestion des catégories d'équipement (CRUD) — Responsable Inventaire.
 */
class CategorieController extends Controller
{
    protected string $layout = 'dashboard';

    public function index(): void
    {
        $this->requireRole('responsable');
        $this->view('categories/index', [
            'categories' => Categorie::withStats(),
        ]);
    }

    public function create(): void
    {
        $this->requireRole('responsable');
        $this->view('categories/form', ['categorie' => null, 'title' => 'Nouvelle catégorie']);
    }

    public function store(): void
    {
        $this->requireRole('responsable');
        $this->verifyCsrf();

        $validator = new Validator($_POST);
        $validator->validate([
            'nom'         => ['Nom', 'required|min:2|max:100'],
            'description' => ['Description', 'max:1000'],
        ]);
        if ($validator->fails()) {
            $this->flash('error', $validator->firstError());
            Router::redirect('categories/creer');
        }
        if (Categorie::findByNom($_POST['nom'])) {
            $this->flash('error', 'Cette catégorie existe déjà.');
            Router::redirect('categories/creer');
        }

        $image = null;
        try {
            $image = Upload::image($_FILES['image'] ?? [], 'categories');
        } catch (\RuntimeException $ex) {
            $this->flash('error', $ex->getMessage());
            Router::redirect('categories/creer');
        }

        Categorie::create([
            'nom'         => trim($_POST['nom']),
            'description' => trim($_POST['description'] ?? ''),
            'image'       => $image,
        ]);
        $this->flash('success', 'La catégorie a été créée.');
        Router::redirect('categories');
    }

    public function edit(string $id): void
    {
        $this->requireRole('responsable');
        $categorie = Categorie::find((int) $id);
        if (!$categorie) {
            (new ErrorController())->notFound();
            return;
        }
        $this->view('categories/form', [
            'categorie' => $categorie,
            'title'     => "Modifier : {$categorie['nom']}",
        ]);
    }

    public function update(string $id): void
    {
        $this->requireRole('responsable');
        $this->verifyCsrf();

        $categorie = Categorie::find((int) $id);
        if (!$categorie) {
            (new ErrorController())->notFound();
            return;
        }

        $validator = new Validator($_POST);
        $validator->validate([
            'nom'         => ['Nom', 'required|min:2|max:100'],
            'description' => ['Description', 'max:1000'],
        ]);
        if ($validator->fails()) {
            $this->flash('error', $validator->firstError());
            Router::redirect('categories/modifier/' . $id);
        }

        $image = $categorie['image'];
        if (!empty($_FILES['image']['name'])) {
            try {
                $newImage = Upload::image($_FILES['image'], 'categories');
                if ($newImage) {
                    Upload::remove($image, 'categories');
                    $image = $newImage;
                }
            } catch (\RuntimeException $ex) {
                $this->flash('error', $ex->getMessage());
                Router::redirect('categories/modifier/' . $id);
            }
        }

        Categorie::update((int) $id, [
            'nom'         => trim($_POST['nom']),
            'description' => trim($_POST['description'] ?? ''),
            'image'       => $image,
        ]);
        $this->flash('success', 'La catégorie a été mise à jour.');
        Router::redirect('categories');
    }

    public function delete(string $id): void
    {
        $this->requireRole('responsable');
        $this->verifyCsrf();

        $categorie = Categorie::find((int) $id);
        if (!$categorie) {
            $this->flash('error', 'Catégorie introuvable.');
        } elseif (Categorie::countEquipements((int) $id) > 0) {
            $this->flash('error', 'Impossible de supprimer : des équipements appartiennent à cette catégorie.');
        } else {
            Upload::remove($categorie['image'], 'categories');
            Categorie::delete((int) $id);
            $this->flash('success', 'La catégorie a été supprimée.');
        }
        Router::redirect('categories');
    }
}
