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
            'promos'           => Equipement::enPromo(4),
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

    /**
     * Sitemap XML — liste les pages publiques indexables.
     */
    public function sitemap(): void
    {
        header('Content-Type: application/xml; charset=utf-8');

        $base  = BASE_URL;
        $today = date('Y-m-d');

        $urls = [
            ['loc' => $base,                'lastmod' => $today, 'priority' => '1.0'],
            ['loc' => $base . 'catalogue',  'lastmod' => $today, 'priority' => '0.8'],
        ];

        foreach (Equipement::all() as $equipement) {
            $urls[] = [
                'loc'      => $base . 'equipement/' . $equipement['id'],
                'lastmod'  => substr((string) ($equipement['cree_le'] ?? ''), 0, 10) ?: $today,
                'priority' => '0.7',
            ];
        }

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . e($url['loc']) . "</loc>\n";
            $xml .= '    <lastmod>' . e($url['lastmod']) . "</lastmod>\n";
            $xml .= '    <priority>' . e($url['priority']) . "</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= "</urlset>\n";

        echo $xml;
        exit;
    }

    /**
     * robots.txt — autorise l'indexation du site public,
     * bloque les zones privées (administration, comptes…).
     */
    public function robots(): void
    {
        header('Content-Type: text/plain; charset=utf-8');

        $bloquees = [
            '/dashboard',
            '/equipements',
            '/categories',
            '/utilisateurs',
            '/profil',
            '/login',
            '/register',
            '/logout',
            '/location',
            '/mes-locations',
            '/demande',
        ];

        echo "User-agent: *\n";
        echo "Disallow:\n";
        foreach ($bloquees as $chemin) {
            echo "Disallow: " . $chemin . "\n";
        }
        echo "\nSitemap: " . BASE_URL . "sitemap.xml\n";
        exit;
    }
}
