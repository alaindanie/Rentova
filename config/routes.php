<?php

/**
 * Déclaration des routes.
 */
use App\Core\Router;

$router = new Router();

/* Pages publiques */
$router->get('',                        'PageController@home');
$router->get('/',                       'PageController@home');
$router->get('catalogue',               'PageController@catalogue');
$router->get('equipement/:id',          'PageController@detail');
$router->get('sitemap.xml',             'PageController@sitemap');
$router->get('robots.txt',              'PageController@robots');

/* Authentification */
$router->get('login',                   'AuthController@loginForm');
$router->post('login',                  'AuthController@login');
$router->get('register',                'AuthController@registerForm');
$router->post('register',               'AuthController@register');
$router->get('logout',                  'AuthController@logout');
$router->post('logout',                 'AuthController@logout');

/* Tableau de bord */
$router->get('dashboard',               'DashboardController@index');

/* Équipements */
$router->get('equipements',             'EquipementController@index');
$router->get('equipements/creer',       'EquipementController@create');
$router->post('equipements',            'EquipementController@store');
$router->get('equipements/modifier/:id','EquipementController@edit');
$router->post('equipements/modifier/:id','EquipementController@update');
$router->get('equipements/supprimer/:id', 'EquipementController@deleteForm');
$router->post('equipements/supprimer/:id','EquipementController@delete');
$router->post('equipements/reparer/:id',  'EquipementController@reparer');
$router->post('equipements/remplacer/:id','EquipementController@remplacer');
$router->post('equipements/maintenance/:id', 'EquipementController@maintenance');
$router->post('equipements/remettre/:id',    'EquipementController@remettreEnService');

/* Catégories */
$router->get('categories',              'CategorieController@index');
$router->get('categories/creer',        'CategorieController@create');
$router->post('categories',             'CategorieController@store');
$router->get('categories/modifier/:id', 'CategorieController@edit');
$router->post('categories/modifier/:id','CategorieController@update');
$router->get('categories/supprimer/:id', 'CategorieController@deleteForm');
$router->post('categories/supprimer/:id','CategorieController@delete');

/* Utilisateurs */
$router->get('utilisateurs',            'UtilisateurController@index');
$router->get('utilisateurs/creer',      'UtilisateurController@create');
$router->post('utilisateurs',           'UtilisateurController@store');
$router->get('utilisateurs/modifier/:id','UtilisateurController@edit');
$router->post('utilisateurs/modifier/:id','UtilisateurController@update');
$router->get('utilisateurs/supprimer/:id', 'UtilisateurController@deleteForm');
$router->post('utilisateurs/supprimer/:id','UtilisateurController@delete');
$router->get('profil',                  'UtilisateurController@profile');
$router->post('profil',                 'UtilisateurController@updateProfile');

/* Locations */
$router->get('locations',               'LocationController@index');
$router->get('location/:id',            'LocationController@show');

/* Côté client */
$router->get('demande/:id',             'LocationController@demande');
$router->post('demande/:id',            'LocationController@storeDemande');
$router->get('mes-locations',           'LocationController@mesLocations');

/* Côté agent */
$router->get('location/:id/confirmer',  'LocationController@confirmationForm');
$router->post('location/:id/confirmer', 'LocationController@confirmer');
$router->get('location/:id/refuser',    'LocationController@refuserForm');
$router->post('location/:id/refuser',   'LocationController@refuser');
$router->get('location/:id/demarrer',   'LocationController@demarrerForm');
$router->post('location/:id/demarrer',  'LocationController@demarrer');
$router->get('location/:id/annuler',    'LocationController@annulerForm');
$router->post('location/:id/annuler',   'LocationController@annuler');
$router->get('location/:id/retour',     'LocationController@retourForm');
$router->post('location/:id/retour',    'LocationController@retour');
$router->get('location/:id/modifier',   'LocationController@editForm');
$router->post('location/:id/modifier',  'LocationController@update');
$router->get('location/:id/supprimer',  'LocationController@deleteForm');
$router->post('location/:id/supprimer', 'LocationController@delete');

/* Documents téléchargeables */
$router->get('location/:id/facture',    'LocationController@facture');
$router->get('location/:id/recu',       'LocationController@recu');
$router->get('location/:id/contrat',    'LocationController@contrat');
$router->get('location/:id/facture/pdf','LocationController@facturePdf');
$router->get('location/:id/recu/pdf',   'LocationController@recuPdf');
$router->get('location/:id/contrat/pdf','LocationController@contratPdf');

/* Dispatch */
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'] ?? '/');
