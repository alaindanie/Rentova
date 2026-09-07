<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Utilisateur;
use App\Models\Equipement;
use App\Models\Categorie;
use App\Models\Location;

/**
 * Tableau de bord : indicateurs selon le rôle connecté.
 */
class DashboardController extends Controller
{
    protected string $layout = 'dashboard';

    public function index(): void
    {
        $this->requireLogin();
        $user = Auth::user();

        if ($user['role'] === 'client') {
            $data = $this->clientDashboard($user);
        } else {
            $data = $this->adminDashboard($user);
        }

        $this->view('dashboard/index', $data);
    }

    /** Tableau de bord agent / responsable : indicateurs globaux. */
    private function adminDashboard(array $user): array
    {
        return [
            'stats' => [
                'equipements'      => Equipement::count(),
                'equipementsDispo' => Equipement::countDisponibles(),
                'categories'       => Categorie::count(),
                'clients'          => Utilisateur::countByRole('client'),
                'agents'           => Utilisateur::countByRole('agent'),
                'locations'        => Location::count(),
                'locationsEnCours' => Location::count('en_cours'),
                'enAttente'        => Location::count('en_attente'),
                'ca'               => Location::chiffreAffaires(),
            ],
            'enAlertes'    => Equipement::enAlerte(),
            'endommages'   => Equipement::endommages(),
            'recentes'     => array_slice(Location::all(), 0, 6),
            'retours'      => Location::retoursImminents(),
            'enRetard'     => Location::countEnRetard(),
            'topEquip'     => Equipement::plusLoues(5),
            'topClients'   => Utilisateur::topClients(5),
            'mois'         => Location::statsMensuelles(6),
            'role'         => $user['role'],
        ];
    }

    /** Tableau de bord client : uniquement ses propres données. */
    private function clientDashboard(array $user): array
    {
        $id = (int) $user['id'];

        return [
            'stats' => [
                'equipements'      => Equipement::countRentedByClient($id),
                'equipementsDispo' => Equipement::countDisponiblesByClient($id),
                'categories'       => Categorie::countRentedByClient($id),
                'clients'          => null, // jamais affiché côté client
                'locations'        => Location::countByClient($id),
                'locationsEnCours' => Location::countEnCoursByClient($id),
                'enAttente'        => Location::countByClient($id, 'en_attente'),
                'ca'               => Location::chiffreAffairesByClient($id),
            ],
            'recentes' => array_slice(Location::ofClient($id), 0, 6),
            'mois'     => Location::statsMensuellesByClient($id),
            'role'     => 'client',
        ];
    }
}
