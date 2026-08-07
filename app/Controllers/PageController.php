<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Equipement;
use App\Models\Categorie;

/**
 * Pages publiques : accueil, catalogue, fiche équipement.
 */
class PageController extends Controller
{
    protected string $layout = 'public';

    public function home(): void
    {
        $this->view('public/home', [
            'equipements'      => Equipement::all(),
            'categories'       => Categorie::withStats(),
            'plusLoues'        => Equipement::plusLoues(4),
            'countEquipements' => Equipement::count(),
            'countCategories'  => Categorie::count(),
            'stockDisponible'  => Equipement::stockTotal(),
        ]);
    }

    public function catalogue(): void
    {
        $filters = [
            'q'            => $_GET['q'] ?? '',
            'categorie_id' => $_GET['categorie_id'] ?? '',
            'prix_min'     => $_GET['prix_min'] ?? '',
            'prix_max'     => $_GET['prix_max'] ?? '',
            'disponible'   => $_GET['disponible'] ?? '',
            'etat'         => $_GET['etat'] ?? '',
            'tri'          => $_GET['tri'] ?? 'nom_asc',
        ];
        // Le filtre d'alerte/stock est réservé à l'inventaire.
        $filters['alerte'] = '';

        $this->view('public/catalogue', [
            'equipements' => Equipement::search($filters),
            'categories'  => Categorie::all(),
            'filters'     => $filters,
            'total'       => count(Equipement::search($filters)),
        ]);
    }

    public function detail(string $id): void
    {
        $equipement = Equipement::find((int) $id);
        if (!$equipement) {
            (new ErrorController())->notFound();
            return;
        }
        $this->view('public/detail', [
            'equipement'     => $equipement,
            'similaires'     => Equipement::search(['q' => '', 'categorie_id' => $equipement['categorie_id'], 'tri' => 'nom_asc']),
            'disponibilites' => \App\Models\Location::ofEquipement($equipement['id']),
        ]);
    }
}
