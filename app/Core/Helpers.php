<?php

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function montant($value): string
{
    return number_format((float) $value, 2, ',', ' ') . ' €';
}

function date_fr($date): string
{
    if (!$date || $date === '0000-00-00') {
        return '—';
    }
    try {
        return (new \DateTime($date))->format(DATE_FORMAT);
    } catch (\Exception $e) {
        return $date;
    }
}

function datetime_fr($datetime): string
{
    if (!$datetime) {
        return '—';
    }
    try {
        return (new \DateTime($datetime))->format('d/m/Y à H:i');
    } catch (\Exception $e) {
        return $datetime;
    }
}

// pour la page detail
function date_longue_fr($date): string
{
    if (!$date || $date === '0000-00-00') {
        return '—';
    }
    try {
        $d = new \DateTime($date);
    } catch (\Exception $e) {
        return (string) $date;
    }
    $jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    $mois  = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    return $jours[(int) $d->format('w')] . ' ' . $d->format('d') . ' ' . $mois[(int) $d->format('n')] . ' ' . $d->format('Y');
}

// coupe le texte si trop long
function excerpt(?string $text, int $length = 90): string
{
    $text = strip_tags((string) $text);
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $length)) . '…';
}

// retourne l'URL d'une image (local ou distante)
function image_url(?string $path, string $fallback = ''): string
{
    if (!$path) {
        return $fallback;
    }
    if (preg_match('#^(https?://|data:)#i', $path)) {
        return $path;
    }
    return IMG . $path;
}

// badge coloré selon le statut de location
function statut_badge(string $statut): string
{
    $map = [
        'en_attente' => ['info',   'En attente'],
        'confirmee'  => ['success', 'Confirmée'],
        'en_cours'   => ['primary', 'En cours'],
        'terminee'   => ['secondary', 'Terminée'],
        'annulee'    => ['danger',  'Annulée'],
        'refusee'    => ['dark',    'Refusée'],
    ];
    [$class, $label] = $map[$statut] ?? ['secondary', $statut];
    return '<span class="badge badge-' . $class . '">' . e($label) . '</span>';
}

function dispo_badge(array $equipement): string
{
    if ((int) $equipement['stock_disponible'] <= 0) {
        return '<span class="badge badge-danger">Rupture</span>';
    }
    if ((int) $equipement['stock_disponible'] <= (int) $equipement['seuil_alerte']) {
        return '<span class="badge badge-warning">Stock bas</span>';
    }
    return '<span class="badge badge-success">Disponible</span>';
}

// ------- promos -------

function est_en_promo(array $equipement): bool
{
    if (empty($equipement['en_promo'])) {
        return false;
    }
    $prix  = (float) $equipement['prix_jour'];
    $promo = (float) ($equipement['prix_promo'] ?? 0);
    return $prix > 0 && $promo > 0 && $promo < $prix;
}

function prix_actuel(array $equipement): float
{
    return est_en_promo($equipement) ? (float) $equipement['prix_promo'] : (float) $equipement['prix_jour'];
}

function promo_badge(array $equipement): string
{
    if (!est_en_promo($equipement)) {
        return '';
    }
    $reduc = (int) round((1 - (float) $equipement['prix_promo'] / (float) $equipement['prix_jour']) * 100);
    return '<span class="badge badge-promo"><i class="fas fa-fire"></i> -' . $reduc . '%</span>';
}

// bloc prix pour les cartes (prix barré si promo)
function price_block(array $equipement): string
{
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
    if (est_en_promo($equipement)) {
        return '<span class="old">' . $fmt($equipement['prix_jour']) . ' €</span><b>' . $fmt($equipement['prix_promo']) . ' €</b><span>/ jour</span>';
    }
    return '<b>' . $fmt($equipement['prix_jour']) . ' €</b><span>/ jour</span>';
}

// "2026-03" -> "mars 26"
function mois_label(string $ym): string
{
    $mois = ['', 'janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    $parts = explode('-', $ym);
    if (count($parts) < 2) {
        return $ym;
    }
    [$y, $m] = $parts;
    $idx = (int) $m;
    if ($idx < 1 || $idx > 12) {
        return $ym;
    }
    return $mois[$idx] . ' ' . substr((string) $y, 2);
}

// nb de jours entre 2 dates
function diff_days(string $d1, string $d2): int
{
    $a = new \DateTime($d1);
    $b = new \DateTime($d2);
    return (int) $a->diff($b)->format('%a');
}

function etat_label(string $etat): string
{
    return [
        'disponible'  => 'Disponible',
        'en_location' => 'En location',
        'maintenance' => 'En maintenance',
        'endommage'   => 'Endommagé',
    ][$etat] ?? $etat;
}

function etat_badge(string $etat): string
{
    $map = [
        'disponible'  => 'success',
        'en_location' => 'primary',
        'maintenance' => 'warning',
        'endommage'   => 'danger',
    ];
    $class = $map[$etat] ?? 'secondary';
    return '<span class="badge badge-' . $class . '">' . e(etat_label($etat)) . '</span>';
}
