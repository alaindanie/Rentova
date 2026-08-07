<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Router;
use App\Core\Validator;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Pdf;
use App\Models\Location;
use App\Models\Equipement;
use App\Models\Categorie;

/**
 * Gestion des locations : demandes, validation, facturation, retours.
 * Flux : en_attente → confirmee → en_cours → terminee (avec retour).
 *        en_attente → refusee | confirmee/en_cours → annulee
 */
class LocationController extends Controller
{
    protected string $layout = 'dashboard';

    /* =========================================================
     *  LISTES
     * ========================================================= */

    public function index(): void
    {
        $this->requireRole(['agent', 'responsable']);
        $statut = $_GET['statut'] ?? '';
        $locations = $statut
            ? Location::all('l.statut = ?', [$statut])
            : Location::all();
        $this->view('locations/index', [
            'locations' => $locations,
            'statut'    => $statut,
            'counts'    => [
                'en_attente' => Location::count('en_attente'),
                'confirmee'  => Location::count('confirmee'),
                'en_cours'   => Location::count('en_cours'),
                'terminee'   => Location::count('terminee'),
                'refusee'    => Location::count('refusee'),
                'annulee'    => Location::count('annulee'),
            ],
        ]);
    }

    public function show(string $id): void
    {
        $this->requireLogin();
        $location = Location::find((int) $id);
        if (!$location) {
            (new ErrorController())->notFound();
            return;
        }
        if (Auth::is('client') && (int) $location['client_id'] !== Auth::id()) {
            (new ErrorController())->forbidden();
            return;
        }
        $this->view('locations/show', ['location' => $location]);
    }

    /** Vue client : mes locations. */
    public function mesLocations(): void
    {
        $this->requireRole('client');
        $this->view('locations/mes', ['locations' => Location::ofClient(Auth::id())]);
    }

    /* =========================================================
     *  DEMANDE DE LOCATION (CLIENT)
     * ========================================================= */

    public function demande(string $id): void
    {
        $this->requireRole('client');
        $equipement = Equipement::find((int) $id);
        if (!$equipement) {
            (new ErrorController())->notFound();
            return;
        }
        $this->view('locations/demande', [
            'equipement' => $equipement,
            'old'        => \App\Core\Session::get('old_location') ?? [],
        ]);
    }

    public function storeDemande(string $id): void
    {
        $this->requireRole('client');
        $this->verifyCsrf();

        $equipement = Equipement::find((int) $id);
        if (!$equipement) {
            (new ErrorController())->notFound();
            return;
        }

        $validator = new Validator($_POST);
        $validator->validate([
            'date_debut' => ['Date de début', 'required|date'],
            'date_fin'   => ['Date de fin', 'required|date|after_or_equal:' . ($_POST['date_debut'] ?? '')],
            'quantite'   => ['Quantité', 'required|int|min_val:1'],
            'note'       => ['Note', 'max:500'],
        ]);
        if ($validator->fails()) {
            $this->flash('error', $validator->firstError());
            \App\Core\Session::set('old_location', $_POST);
            Router::redirect('demande/' . $id);
        }

        $debut   = $_POST['date_debut'];
        $fin     = $_POST['date_fin'];
        $qte     = max(1, (int) $_POST['quantite']);
        $duree   = max(1, diff_days($debut, $fin));

        if ($fin < date('Y-m-d')) {
            $this->flash('error', 'La période de location doit être dans le futur.');
            \App\Core\Session::set('old_location', $_POST);
            Router::redirect('demande/' . $id);
        }
        if ($qte > (int) $equipement['stock_disponible']) {
            $this->flash('error', "Stock insuffisant : {$equipement['stock_disponible']} exemplaire(s) disponible(s).");
            \App\Core\Session::set('old_location', $_POST);
            Router::redirect('demande/' . $id);
        }
        if (!Location::disponibilite((int) $id, $debut, $fin, $qte)) {
            $this->flash('error', 'L\'équipement n\'est pas disponible sur cette période (déjà réservé).');
            \App\Core\Session::set('old_location', $_POST);
            Router::redirect('demande/' . $id);
        }

        $montantBase = (float) $equipement['prix_jour'] * $duree * $qte;

        Location::create([
            'reference'     => Location::generateReference(),
            'client_id'     => Auth::id(),
            'equipement_id' => (int) $id,
            'date_debut'    => $debut,
            'date_fin'      => $fin,
            'quantite'      => $qte,
            'montant_base'  => $montantBase,
            'montant_frais' => 0,
            'statut'        => 'en_attente',
            'note'          => trim($_POST['note'] ?? ''),
        ]);

        \App\Core\Session::remove('old_location');
        $this->flash('success', 'Votre demande de location a bien été enregistrée. Elle sera traitée par notre équipe.');
        Router::redirect('mes-locations');
    }

    /* =========================================================
     *  VALIDATION PAR L'AGENT
     * ========================================================= */

    public function confirmer(string $id): void
    {
        $this->requireRole(['agent', 'responsable']);
        $this->verifyCsrf();

        $location = Location::find((int) $id);
        if (!$location) {
            $this->flash('error', 'Location introuvable.');
        } elseif ($location['statut'] !== 'en_attente') {
            $this->flash('error', 'Cette demande a déjà été traitée.');
        } elseif (!Location::disponibilite((int) $location['equipement_id'], $location['date_debut'], $location['date_fin'], (int) $location['quantite'], (int) $id)) {
            $this->flash('error', 'Impossible de confirmer : la quantité demandée n\'est plus disponible sur cette période.');
        } elseif (Location::confirmer((int) $id, Auth::id())) {
            $this->flash('success', 'Location confirmée. Le stock a été réservé.');
        } else {
            $this->flash('error', 'Une erreur est survenue lors de la confirmation.');
        }
        Router::redirect('location/' . $id);
    }

    public function refuser(string $id): void
    {
        $this->requireRole(['agent', 'responsable']);
        $this->verifyCsrf();

        $location = Location::find((int) $id);
        if ($location && $location['statut'] === 'en_attente') {
            Location::refuser((int) $id, Auth::id());
            $this->flash('success', 'La demande a été refusée.');
        } else {
            $this->flash('error', 'Impossible de refuser cette demande.');
        }
        Router::redirect('location/' . $id);
    }

    public function demarrer(string $id): void
    {
        $this->requireRole(['agent', 'responsable']);
        $this->verifyCsrf();

        $location = Location::find((int) $id);
        if ($location && $location['statut'] === 'confirmee') {
            Location::demarrer((int) $id);
            $this->flash('success', 'La location est passée au statut « En cours ».');
        } else {
            $this->flash('error', 'Impossible de démarrer cette location.');
        }
        Router::redirect('location/' . $id);
    }

    public function annuler(string $id): void
    {
        $this->requireRole(['agent', 'responsable']);
        $this->verifyCsrf();

        $location = Location::find((int) $id);
        if ($location && in_array($location['statut'], ['confirmee', 'en_cours', 'en_attente'], true)) {
            Location::annuler((int) $id);
            $this->flash('success', 'La location a été annulée et le stock libéré.');
        } else {
            $this->flash('error', 'Cette location ne peut pas être annulée.');
        }
        Router::redirect('location/' . $id);
    }

    /* =========================================================
     *  RETOUR D'ÉQUIPEMENT
     * ========================================================= */

    public function retourForm(string $id): void
    {
        $this->requireRole(['agent', 'responsable']);
        $location = Location::find((int) $id);
        if (!$location || !in_array($location['statut'], ['confirmee', 'en_cours'], true)) {
            $this->flash('error', 'Ce retour n\'est pas possible : la location n\'est pas active.');
            Router::redirect('location/' . $id);
        }
        $this->view('locations/retour', ['location' => $location]);
    }

    public function retour(string $id): void
    {
        $this->requireRole(['agent', 'responsable']);
        $this->verifyCsrf();

        $location = Location::find((int) $id);
        if (!$location || !in_array($location['statut'], ['confirmee', 'en_cours'], true)) {
            $this->flash('error', 'Ce retour n\'est pas possible.');
            Router::redirect('location/' . $id);
        }

        $validator = new Validator($_POST);
        $validator->validate([
            'condition_retour' => ['Condition', 'required|in:neuf,tres_bon,bon,use,endommage'],
            'commentaire'      => ['Commentaire', 'max:500'],
            'montant_frais'    => ['Frais additionnels', 'numeric|min_val:0|max_val:' . (float) $location['montant_base']],
            'date_retour'      => ['Date de retour', 'date'],
        ]);
        if ($validator->fails()) {
            $this->flash('error', $validator->firstError());
            Router::redirect('location/' . $id . '/retour');
        }

        if (Location::enregistrerRetour((int) $id, [
            'condition_retour'      => $_POST['condition_retour'],
            'commentaire'           => $_POST['commentaire'] ?? '',
            'montant_frais'         => (float) ($_POST['montant_frais'] ?? 0),
            'date_retour_effectif'  => $_POST['date_retour'] ?? date('Y-m-d'),
        ])) {
            $this->flash('success', 'Retour enregistré : stock réapprovisionné et frais calculés.');
        } else {
            $this->flash('error', 'Erreur lors de l\'enregistrement du retour.');
        }
        Router::redirect('location/' . $id);
    }

    /* =========================================================
     *  DOCUMENTS : FACTURE, REÇU, CONTRAT
     * ========================================================= */

    private function checkDocumentAccess(array $location): void
    {
        if (!Auth::check()) {
            Router::redirect('login');
        }
        if (Auth::is('client') && (int) $location['client_id'] !== Auth::id()) {
            (new ErrorController())->forbidden();
            exit;
        }
    }

    public function facture(string $id): void
    {
        $this->requireLogin();
        $location = Location::find((int) $id);
        if (!$location) {
            (new ErrorController())->notFound();
            return;
        }
        $this->checkDocumentAccess($location);
        $this->renderDocument('locations/facture', ['location' => $location]);
    }

    public function recu(string $id): void
    {
        $this->requireLogin();
        $location = Location::find((int) $id);
        if (!$location) {
            (new ErrorController())->notFound();
            return;
        }
        $this->checkDocumentAccess($location);
        $this->renderDocument('locations/recu', ['location' => $location]);
    }

    public function contrat(string $id): void
    {
        $this->requireLogin();
        $location = Location::find((int) $id);
        if (!$location) {
            (new ErrorController())->notFound();
            return;
        }
        $this->checkDocumentAccess($location);
        $this->renderDocument('locations/contrat', ['location' => $location]);
    }

    /* --------------------------- Téléchargements PDF --------------------------- */

    public function facturePdf(string $id): void
    {
        $this->requireLogin();
        $location = Location::find((int) $id);
        if (!$location) {
            (new ErrorController())->notFound();
            return;
        }
        $this->checkDocumentAccess($location);
        $this->streamPdf($location, 'facture', Pdf::facture($location));
    }

    public function recuPdf(string $id): void
    {
        $this->requireLogin();
        $location = Location::find((int) $id);
        if (!$location) {
            (new ErrorController())->notFound();
            return;
        }
        $this->checkDocumentAccess($location);
        $this->streamPdf($location, 'recu', Pdf::recu($location));
    }

    public function contratPdf(string $id): void
    {
        $this->requireLogin();
        $location = Location::find((int) $id);
        if (!$location) {
            (new ErrorController())->notFound();
            return;
        }
        $this->checkDocumentAccess($location);
        $this->streamPdf($location, 'contrat', Pdf::contrat($location));
    }

    private function streamPdf(array $location, string $type, string $pdf): void
    {
        $filename = strtolower($type) . '-' . $location['reference'] . '.pdf';
        $filename = preg_replace('/[^a-zA-Z0-9\-_.]+/', '-', $filename);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }

    private function renderDocument(string $view, array $data): void
    {
        $this->layout = 'document';
        $this->view($view, $data);
    }
}
