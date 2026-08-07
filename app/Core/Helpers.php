<?php

/**
 * Fonctions utilitaires globales.
 */

/** Échappe une chaîne pour l'affichage HTML. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Formate un nombre en montant €. */
function montant($value): string
{
    return number_format((float) $value, 2, ',', ' ') . ' €';
}

/** Formate une date française. */
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

/** Date + heure française. */
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

/** Date longue en français (ex : « vendredi 07 août 2026 »). */
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

/** Tranche le texte avec "…". */
function excerpt(?string $text, int $length = 90): string
{
    $text = strip_tags((string) $text);
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $length)) . '…';
}

/** Détermine l'URL d'une image (fichier local ou base64/URL distante). */
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

/** Badge de statut coloré pour les locations. */
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

/** Badge de disponibilité d'un équipement. */
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

/** Nombre de jours entre deux dates. */
function diff_days(string $d1, string $d2): int
{
    $a = new \DateTime($d1);
    $b = new \DateTime($d2);
    return (int) $a->diff($b)->format('%a');
}
