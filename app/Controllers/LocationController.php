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
use App\Models\Utilisateur;

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
     *  DEMANDE DE LOCATION (CLIENT ou SAISIE PAR L'AGENT)
     * ========================================================= */

    public function demande(string $id): void
    {
        $this->requireLogin();
        $equipement = Equipement::find((int) $id);
        if (!$equipement) {
            (new ErrorController())->notFound();
            return;
        }
        $role = Auth::role();
        if ($role === 'client') {
            $this->requireRole('client');
            $clients = [];
        } else {
            $this->requireRole(['agent', 'responsable']);
            $clients = Utilisateur::clients();
        }
        $this->view('locations/demande', [
            'equipement' => $equipement,
            'old'        => \App\Core\Session::get('old_location') ?? [],
            'clients'    => $clients,
        ]);
    }

    public function storeDemande(string $id): void
    {
        $this->requireLogin();
        $role = Auth::role();
        if ($role === 'client') {
            $this->requireRole('client');
            $clientId = Auth::id();
        } else {
            $this->requireRole(['agent', 'responsable']);
            $clientId = (int) ($_POST['client_id'] ?? 0);
            $client = Utilisateur::find($clientId);
            if (!$client || $client['role'] !== 'client') {
                $this->flash('error', 'Veuillez sélectionner le client concerné par cette demande.');
                \App\Core\Session::set('old_location', $_POST);
                Router::redirect('demande/' . $id);
            }
        }
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
            'cgv'        => ['Acceptation des conditions générales', 'required'],
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
        if ($equipement['etat'] !== 'disponible') {
            $this->flash('error', 'Cet équipement n\'est pas disponible à la location pour le moment.');
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
            'client_id'     => $clientId,
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
        if ($role === 'client') {
            $this->flash('success', 'Votre demande de location a bien été enregistrée. Elle sera traitée par notre équipe.');
            Router::redirect('mes-locations');
        }
        $this->flash('success', 'La demande de location a été enregistrée pour le compte du client. Elle est à traiter.');
        Router::redirect('locations');
    }

    /* =========================================================
     *  MODIFICATION D'UNE DEMANDE EN ATTENTE (CRUD update)
     * ========================================================= */

    public function editForm(string $id): void
    {
        $this->requireRole(['agent', 'responsable']);

        $location = Location::find((int) $id);
        if (!$location) {
            $this->flash('error', 'Location introuvable.');
            Router::redirect('locations');
        }
        if ($location['statut'] !== 'en_attente') {
            $this->flash('error', 'Seules les demandes en attente peuvent être modifiées.');
            Router::redirect('location/' . $id);
        }

        $this->view('locations/edit', [
            'location' => $location,
            'old'      => \App\Core\Session::get('old_edit_location') ?? [],
        ]);
    }

    public function update(string $id): void
    {
        $this->requireRole(['agent', 'responsable']);
        $this->verifyCsrf();

        $location = Location::find((int) $id);
        if (!$location) {
            $this->flash('error', 'Location introuvable.');
            Router::redirect('locations');
        }
        if ($location['statut'] !== 'en_attente') {
            $this->flash('error', 'Cette demande ne peut plus être modifiée.');
            Router::redirect('location/' . $id);
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
            \App\Core\Session::set('old_edit_location', $_POST);
            Router::redirect('location/' . $id . '/modifier');
        }

        $debut = $_POST['date_debut'];
        $fin   = $_POST['date_fin'];
        $qte   = max(1, (int) $_POST['quantite']);
        if ($fin < date('Y-m-d')) {
            $this->flash('error', 'La période de location doit être dans le futur.');
            Router::redirect('location/' . $id . '/modifier');
        }

        $equipement = Equipement::find((int) $location['equipement_id']);
        if (!$equipement || $qte > (int) $equipement['stock_disponible']) {
            $this->flash('error', 'Stock insuffisant pour la quantité demandée.');
            Router::redirect('location/' . $id . '/modifier');
        }
        if (!Location::disponibilite((int) $location['equipement_id'], $debut, $fin, $qte, (int) $id)) {
            $this->flash('error', 'L\'équipement n\'est pas disponible sur cette période (déjà réservé).');
            Router::redirect('location/' . $id . '/modifier');
        }

        $montantBase = round((float) $equipement['prix_jour'] * max(1, diff_days($debut, $fin)) * $qte, 2);

        Location::update((int) $id, [
            'equipement_id' => (int) $location['equipement_id'],
            'date_debut'    => $debut,
            'date_fin'      => $fin,
            'quantite'      => $qte,
            'montant_base'  => $montantBase,
            'montant_frais' => 0,
            'statut'        => 'en_attente',
            'note'          => trim($_POST['note'] ?? ''),
        ]);

        \App\Core\Session::remove('old_edit_location');
        $this->flash('success', 'La demande ' . $location['reference'] . ' a été mise à jour.');
        Router::redirect('location/' . $id);
    }

    /* =========================================================
     *  SUPPRESSION DÉFINITIVE (CRUD delete — demandes non traitées)
     * ========================================================= */

    public function deleteForm(string $id): void
    {
        $this->requireRole(['agent', 'responsable']);

        $location = Location::find((int) $id);
        if (!$location) {
            $this->flash('error', 'Location introuvable.');
            Router::redirect('locations');
        }
        if (!in_array($location['statut'], ['en_attente', 'refusee', 'annulee'], true)) {
            $this->flash('error', 'Cette location ne peut pas être supprimée définitivement (utilisez l\'annulation).');
            Router::redirect('location/' . $id);
        }

        $this->view('confirm/index', [
            'confirmTitle'   => 'Supprimer la demande ' . $location['reference'],
            'confirmMessage' => 'Vous êtes sur le point de supprimer définitivement cette demande de location.',
            'confirmMessage2' => 'Cette action est irréversible et ne libère aucun stock (demande non traitée).',
            'confirmIcon'    => 'fa-trash',
            'confirmPost'    => BASE_URL . 'location/' . $id . '/supprimer',
            'confirmButton'  => 'Supprimer définitivement',
            'confirmButtonClass' => 'btn-danger-ghost',
            'confirmButtonIcon'  => 'fa-trash',
            'backUrl'        => BASE_URL . 'location/' . $id,
            'backLabel'      => 'Retour à la location',
            'confirmRows'    => [
                ['label' => 'Référence',  'value' => $location['reference']],
                ['label' => 'Équipement', 'value' => $location['equipement_nom'] . ' — ' . $location['marque'] . ' ' . $location['modele']],
                ['label' => 'Client',      'value' => $location['client_prenom'] . ' ' . $location['client_nom']],
                ['label' => 'Période',     'value' => date_fr($location['date_debut']) . ' → ' . date_fr($location['date_fin'])],
                ['label' => 'Statut',      'value' => statut_badge($location['statut'])],
            ],
        ]);
    }

    public function delete(string $id): void
    {
        $this->requireRole(['agent', 'responsable']);
        $this->verifyCsrf();

        $location = Location::find((int) $id);
        if (!$location) {
            $this->flash('error', 'Location introuvable.');
        } elseif (!in_array($location['statut'], ['en_attente', 'refusee', 'annulee'], true)) {
            $this->flash('error', 'Cette location ne peut pas être supprimée : elle est confirmée, en cours ou terminée.');
        } elseif (Location::delete((int) $id)) {
            $this->flash('success', 'La demande ' . $location['reference'] . ' a été supprimée définitivement.');
        } else {
            $this->flash('error', 'Erreur lors de la suppression de la demande.');
        }
        Router::redirect('locations');
    }

    /* =========================================================
     *  VALIDATION PAR L'AGENT
     * ========================================================= */

    public function confirmationForm(string $id): void
    {
        $this->requireRole(['agent', 'responsable']);

        $location = Location::find((int) $id);
        if (!$location) {
            $this->flash('error', 'Location introuvable.');
            Router::redirect('locations');
        }
        if ($location['statut'] !== 'en_attente') {
            $this->flash('error', 'Cette demande a déjà été traitée.');
            Router::redirect('location/' . $id);
        }

        $this->view('locations/confirmer', ['location' => $location]);
    }

    public function refuserForm(string $id): void
    {
        $this->requireRole(['agent', 'responsable']);

        $location = Location::find((int) $id);
        if (!$location) {
            $this->flash('error', 'Location introuvable.');
            Router::redirect('locations');
        }
        if ($location['statut'] !== 'en_attente') {
            $this->flash('error', 'Cette demande a déjà été traitée.');
            Router::redirect('location/' . $id);
        }

        $this->view('confirm/index', $this->confirmData(
            $location,
            'Refuser la demande ' . $location['reference'],
            'Vous êtes sur le point de refuser cette demande de location.',
            'Le client sera notifié du refus et aucun stock ne sera réservé.',
            'fa-xmark',
            'location/' . $id . '/refuser',
            'Refuser la demande',
            'btn-danger-ghost',
            'fa-xmark'
        ));
    }

    public function demarrerForm(string $id): void
    {
        $this->requireRole(['agent', 'responsable']);

        $location = Location::find((int) $id);
        if (!$location) {
            $this->flash('error', 'Location introuvable.');
            Router::redirect('locations');
        }
        if ($location['statut'] !== 'confirmee') {
            $this->flash('error', 'Cette location ne peut pas être démarrée.');
            Router::redirect('location/' . $id);
        }

        $this->view('confirm/index', $this->confirmData(
            $location,
            'Démarrer la location ' . $location['reference'],
            'Vous êtes sur le point de démarrer cette location.',
            'La période de location commence aujourd\'hui et la location passera au statut « En cours ».',
            'fa-play',
            'location/' . $id . '/demarrer',
            'Démarrer la location',
            'btn-primary',
            'fa-play'
        ));
    }

    public function annulerForm(string $id): void
    {
        $this->requireRole(['agent', 'responsable']);

        $location = Location::find((int) $id);
        if (!$location) {
            $this->flash('error', 'Location introuvable.');
            Router::redirect('locations');
        }
        if (!in_array($location['statut'], ['en_attente', 'confirmee', 'en_cours'], true)) {
            $this->flash('error', 'Cette location ne peut pas être annulée.');
            Router::redirect('location/' . $id);
        }

        $this->view('confirm/index', $this->confirmData(
            $location,
            'Annuler la location ' . $location['reference'],
            'Vous êtes sur le point d\'annuler cette location.',
            'Le stock réservé sera libéré et la location passera au statut « Annulée ».',
            'fa-ban',
            'location/' . $id . '/annuler',
            'Annuler la location',
            'btn-danger-ghost',
            'fa-ban'
        ));
    }

    /**
     * Construit les données communes d'une page de confirmation PHP.
     */
    private function confirmData(array $location, string $title, string $message, string $message2, string $icon, string $post, string $button, string $buttonClass, string $buttonIcon): array
    {
        return [
            'confirmTitle'  => $title,
            'confirmMessage' => $message,
            'confirmMessage2' => $message2,
            'confirmIcon'   => $icon,
            'confirmPost'   => BASE_URL . $post,
            'confirmButton' => $button,
            'confirmButtonClass' => $buttonClass,
            'confirmButtonIcon'  => $buttonIcon,
            'backUrl'       => BASE_URL . 'location/' . $location['id'],
            'backLabel'     => 'Retour à la location',
            'confirmRows'   => [
                ['label' => 'Référence',  'value' => $location['reference']],
                ['label' => 'Équipement', 'value' => $location['equipement_nom'] . ' — ' . $location['marque'] . ' ' . $location['modele']],
                ['label' => 'Client',      'value' => $location['client_prenom'] . ' ' . $location['client_nom']],
                ['label' => 'Période',     'value' => date_fr($location['date_debut']) . ' → ' . date_fr($location['date_fin'])],
                ['label' => 'Durée',       'value' => (int) $location['duree'] . ' jour(s)'],
                ['label' => 'Quantité',    'value' => '× ' . (int) $location['quantite']],
                ['label' => 'Montant',     'value' => montant($location['montant_total'])],
            ],
        ];
    }

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
            $this->flash('success', 'Location confirmée et démarrée. Le stock a été réservé.');
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
            if ($_POST['condition_retour'] === 'endommage') {
                $this->flash('warning', 'Retour enregistré, mais l\'équipement est revenu endommagé. Prévoyez une réparation ou un remplacement (alerte visible sur le tableau de bord).');
            } else {
                $this->flash('success', 'Retour enregistré : l\'équipement est de nouveau disponible et le stock a été réapprovisionné.');
            }
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
