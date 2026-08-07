<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Router;
use App\Core\Validator;
use App\Core\Upload;
use App\Core\Auth;
use App\Models\Equipement;
use App\Models\Categorie;

/**
 * Gestion des équipements (CRUD + recherche multicritères) — Responsable Inventaire.
 */
class EquipementController extends Controller
{
    protected string $layout = 'dashboard';

    /** Liste + recherche multicritères. */
    public function index(): void
    {
        $this->requireRole('responsable');
        $filters = [
            'q'            => $_GET['q'] ?? '',
            'categorie_id' => $_GET['categorie_id'] ?? '',
            'prix_min'     => $_GET['prix_min'] ?? '',
            'prix_max'     => $_GET['prix_max'] ?? '',
            'disponible'   => $_GET['disponible'] ?? '',
            'alerte'       => $_GET['alerte'] ?? '',
            'etat'         => $_GET['etat'] ?? '',
            'tri'          => $_GET['tri'] ?? 'nom_asc',
        ];

        $this->view('equipements/index', [
            'equipements' => Equipement::search($filters),
            'categories'  => Categorie::all(),
            'filters'     => $filters,
            'alertes'     => Equipement::enAlerte(),
        ]);
    }

    public function create(): void
    {
        $this->requireRole('responsable');
        $this->view('equipements/form', [
            'equipement' => null,
            'categories' => Categorie::all(),
            'title'      => 'Nouvel équipement',
        ]);
    }

    public function store(): void
    {
        $this->requireRole('responsable');
        $this->verifyCsrf();

        $errors = $this->validateInput($_POST);
        if ($errors) {
            $this->flash('error', $errors[0]);
            Router::redirect('equipements/creer');
        }

        $image = null;
        try {
            $image = Upload::image($_FILES['image'] ?? [], 'equipements');
        } catch (\RuntimeException $ex) {
            $this->flash('error', $ex->getMessage());
            Router::redirect('equipements/creer');
        }

        Equipement::create($this->payload($_POST, $image));
        $this->flash('success', "L'équipement a été ajouté au catalogue avec succès.");
        Router::redirect('equipements');
    }

    public function edit(string $id): void
    {
        $this->requireRole('responsable');
        $equipement = Equipement::find((int) $id);
        if (!$equipement) {
            (new ErrorController())->notFound();
            return;
        }
        $this->view('equipements/form', [
            'equipement' => $equipement,
            'categories' => Categorie::all(),
            'title'      => "Modifier : {$equipement['nom']}",
        ]);
    }

    public function update(string $id): void
    {
        $this->requireRole('responsable');
        $this->verifyCsrf();

        $equipement = Equipement::find((int) $id);
        if (!$equipement) {
            (new ErrorController())->notFound();
            return;
        }

        $errors = $this->validateInput($_POST, true);
        if ($errors) {
            $this->flash('error', $errors[0]);
            Router::redirect('equipements/modifier/' . $id);
        }

        $image = $equipement['image'];
        if (!empty($_FILES['image']['name'])) {
            try {
                $newImage = Upload::image($_FILES['image'], 'equipements');
                if ($newImage) {
                    Upload::remove($image, 'equipements');
                    $image = $newImage;
                }
            } catch (\RuntimeException $ex) {
                $this->flash('error', $ex->getMessage());
                Router::redirect('equipements/modifier/' . $id);
            }
        }

        Equipement::update((int) $id, $this->payload($_POST + ['id' => $id], $image, true));
        $this->flash('success', "L'équipement « {$equipement['nom']} » a été mis à jour.");
        Router::redirect('equipements');
    }

    public function delete(string $id): void
    {
        $this->requireRole('responsable');
        $this->verifyCsrf();

        $equipement = Equipement::find((int) $id);
        if (!$equipement) {
            $this->flash('error', 'Équipement introuvable.');
        } else {
            $locations = \App\Models\Location::ofEquipement($equipement['id']);
            if ($locations) {
                $this->flash('error', 'Impossible de supprimer : cet équipement possède des locations associées.');
            } else {
                Upload::remove($equipement['image'], 'equipements');
                Equipement::delete((int) $id);
                $this->flash('success', "L'équipement « {$equipement['nom']} » a été supprimé.");
            }
        }
        Router::redirect('equipements');
    }

    /* --------------------------- Helpers --------------------------- */

    private function validateInput(array $d, bool $isUpdate = false): array
    {
        $validator = new Validator($d);
        $validator->validate([
            'nom'          => ['Nom', 'required|min:2|max:150'],
            'categorie_id' => ['Catégorie', 'required|int|min_val:1'],
            'marque'       => ['Marque', 'max:100'],
            'modele'       => ['Modèle', 'max:100'],
            'prix_jour'    => ['Prix / jour', 'required|numeric|min_val:0'],
            'stock_total'  => ['Stock total', 'required|int|min_val:0'],
            'seuil_alerte' => ['Seuil d\'alerte', 'required|int|min_val:0'],
            'etat'         => ['État', 'in:disponible,indisponible,maintenance'],
            'description'  => ['Description', 'max:1000'],
        ]);
        return $validator->errors() ? array_values($validator->errors())[0] : [];
    }

    private function payload(array $d, ?string $image, bool $isUpdate = false): array
    {
        $stockTotal = (int) $d['stock_total'];
        $stockDispo = $stockTotal;
        if ($isUpdate) {
            // Préserve l'écart actuel entre disponible et total.
            $equipement = Equipement::find((int) $d['id']);
            $ecart = $equipement ? $equipement['stock_total'] - $equipement['stock_disponible'] : 0;
            $stockDispo = max(0, $stockTotal - $ecart);
        }
        return [
            'categorie_id'    => (int) $d['categorie_id'],
            'nom'             => trim($d['nom']),
            'description'     => trim($d['description'] ?? ''),
            'marque'          => trim($d['marque'] ?? ''),
            'modele'          => trim($d['modele'] ?? ''),
            'prix_jour'       => (float) $d['prix_jour'],
            'stock_total'     => $stockTotal,
            'stock_disponible'=> $stockDispo,
            'seuil_alerte'    => (int) $d['seuil_alerte'],
            'image'           => $image,
            'etat'            => $d['etat'] ?? 'disponible',
        ];
    }
}
