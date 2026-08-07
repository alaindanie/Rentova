<?php

/**
 * Déclaration des routes de l'application.
 * Format :  $router->get('/url', 'Controleur@action');
 */
use App\Core\Router;

$router = new Router();

/* --------------------------- Pages publiques --------------------------- */
$router->get('',                        'PageController@home');
$router->get('/',                       'PageController@home');
$router->get('catalogue',               'PageController@catalogue');
$router->get('equipement/:id',          'PageController@detail');

/* --------------------------- Authentification --------------------------- */
$router->get('login',                   'AuthController@loginForm');
$router->post('login',                  'AuthController@login');
$router->get('register',                'AuthController@registerForm');
$router->post('register',               'AuthController@register');
$router->get('logout',                  'AuthController@logout');
$router->post('logout',                 'AuthController@logout');

/* --------------------------- Tableau de bord --------------------------- */
$router->get('dashboard',               'DashboardController@index');

/* --------------------------- Équipements (Inventaire) --------------------------- */
$router->get('equipements',             'EquipementController@index');
$router->get('equipements/creer',       'EquipementController@create');
$router->post('equipements',            'EquipementController@store');
$router->get('equipements/modifier/:id','EquipementController@edit');
$router->post('equipements/modifier/:id','EquipementController@update');
$router->post('equipements/supprimer/:id','EquipementController@delete');

/* --------------------------- Catégories (Inventaire) --------------------------- */
$router->get('categories',              'CategorieController@index');
$router->get('categories/creer',        'CategorieController@create');
$router->post('categories',             'CategorieController@store');
$router->get('categories/modifier/:id', 'CategorieController@edit');
$router->post('categories/modifier/:id','CategorieController@update');
$router->post('categories/supprimer/:id','CategorieController@delete');

/* --------------------------- Utilisateurs --------------------------- */
$router->get('utilisateurs',            'UtilisateurController@index');
$router->get('utilisateurs/creer',      'UtilisateurController@create');
$router->post('utilisateurs',           'UtilisateurController@store');
$router->get('utilisateurs/modifier/:id','UtilisateurController@edit');
$router->post('utilisateurs/modifier/:id','UtilisateurController@update');
$router->post('utilisateurs/supprimer/:id','UtilisateurController@delete');
$router->get('profil',                  'UtilisateurController@profile');
$router->post('profil',                 'UtilisateurController@updateProfile');

/* --------------------------- Locations --------------------------- */
$router->get('locations',               'LocationController@index');
$router->get('location/:id',            'LocationController@show');

/* Client : demande & historique */
$router->get('demande/:id',             'LocationController@demande');
$router->post('demande/:id',            'LocationController@storeDemande');
$router->get('mes-locations',           'LocationController@mesLocations');

/* Agent : flux de validation */
$router->post('location/:id/confirmer', 'LocationController@confirmer');
$router->post('location/:id/refuser',   'LocationController@refuser');
$router->post('location/:id/demarrer',  'LocationController@demarrer');
$router->post('location/:id/annuler',   'LocationController@annuler');
$router->get('location/:id/retour',     'LocationController@retourForm');
$router->post('location/:id/retour',    'LocationController@retour');

/* Documents téléchargeables */
$router->get('location/:id/facture',    'LocationController@facture');
$router->get('location/:id/recu',       'LocationController@recu');
$router->get('location/:id/contrat',    'LocationController@contrat');
$router->get('location/:id/facture/pdf','LocationController@facturePdf');
$router->get('location/:id/recu/pdf',   'LocationController@recuPdf');
$router->get('location/:id/contrat/pdf','LocationController@contratPdf');

/* Dispatch */
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'] ?? '/');
