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

        $data = [
            'stats' => [
                'equipements'      => Equipement::count(),
                'equipementsDispo' => Equipement::countDisponibles(),
                'categories'       => Categorie::count(),
                'clients'          => Utilisateur::countByRole('client'),
                'locations'        => Location::count(),
                'locationsEnCours' => Location::count('en_cours') + Location::count('confirmee'),
                'enAttente'        => Location::count('en_attente'),
                'ca'               => Location::chiffreAffaires(),
            ],
            'enAlertes'    => Equipement::enAlerte(),
            'recentes'     => array_slice(Location::all(), 0, 6),
            'retours'      => Location::retoursImminents(),
            'enRetard'     => Location::countEnRetard(),
            'topEquip'     => Equipement::plusLoues(5),
            'topClients'   => Utilisateur::topClients(5),
            'mois'         => Location::statsMensuelles(6),
            'role'         => $user['role'],
        ];

        $this->view('dashboard/index', $data);
    }
}
