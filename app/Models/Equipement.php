<?php

namespace App\Models;

use App\Core\Database;

class Equipement
{
    public static function all(): array
    {
        return Database::fetchAll(
            'SELECT e.*, c.nom AS categorie_nom
             FROM equipements e
             INNER JOIN categories c ON c.id = e.categorie_id
             ORDER BY e.nom ASC'
        );
    }

    public static function find(int $id): ?array
    {
        return Database::fetch(
            'SELECT e.*, c.nom AS categorie_nom, c.description AS categorie_description
             FROM equipements e
             INNER JOIN categories c ON c.id = e.categorie_id
             WHERE e.id = ?',
            [$id]
        );
    }

    public static function create(array $data): int
    {
        $stockTotal   = (int) ($data['stock_total'] ?? 0);
        $stockDispo   = (int) ($data['stock_disponible'] ?? $stockTotal);
        return Database::insert(
            'INSERT INTO equipements
                (categorie_id, nom, description, marque, modele, prix_jour,
                 en_promo, prix_promo,
                 stock_total, stock_disponible, seuil_alerte, image, etat)
             VALUES
                (:categorie_id, :nom, :description, :marque, :modele, :prix_jour,
                 :en_promo, :prix_promo,
                 :stock_total, :stock_disponible, :seuil_alerte, :image, :etat)',
            [
                ':categorie_id'    => $data['categorie_id'],
                ':nom'             => $data['nom'],
                ':description'     => $data['description'] ?? null,
                ':marque'          => $data['marque'] ?? null,
                ':modele'          => $data['modele'] ?? null,
                ':prix_jour'       => $data['prix_jour'],
                ':en_promo'        => (int) ($data['en_promo'] ?? 0),
                ':prix_promo'      => $data['prix_promo'] ?? null,
                ':stock_total'     => $stockTotal,
                ':stock_disponible'=> $stockDispo,
                ':seuil_alerte'    => (int) ($data['seuil_alerte'] ?? 0),
                ':image'           => $data['image'] ?? null,
                ':etat'            => $data['etat'] ?? 'disponible',
            ]
        );
    }

    public static function update(int $id, array $data): bool
    {
        return Database::query(
            'UPDATE equipements SET
                categorie_id = :categorie_id, nom = :nom, description = :description,
                marque = :marque, modele = :modele, prix_jour = :prix_jour,
                en_promo = :en_promo, prix_promo = :prix_promo,
                stock_total = :stock_total, stock_disponible = :stock_disponible,
                seuil_alerte = :seuil_alerte, image = :image, etat = :etat
             WHERE id = :id',
            [
                ':categorie_id'    => $data['categorie_id'],
                ':nom'             => $data['nom'],
                ':description'     => $data['description'] ?? null,
                ':marque'          => $data['marque'] ?? null,
                ':modele'          => $data['modele'] ?? null,
                ':prix_jour'       => $data['prix_jour'],
                ':en_promo'        => (int) ($data['en_promo'] ?? 0),
                ':prix_promo'      => $data['prix_promo'] ?? null,
                ':stock_total'     => (int) ($data['stock_total'] ?? 0),
                ':stock_disponible'=> max(0, (int) ($data['stock_disponible'] ?? $data['stock_total'] ?? 0)),
                ':seuil_alerte'    => (int) ($data['seuil_alerte'] ?? 0),
                ':image'           => $data['image'] ?? null,
                ':etat'            => $data['etat'] ?? 'disponible',
                ':id'              => $id,
            ]
        )->rowCount() >= 0;
    }

    public static function delete(int $id): bool
    {
        return Database::query('DELETE FROM equipements WHERE id = ?', [$id])->rowCount() > 0;
    }

    // ------- stock -------

    public static function incrementerStock(int $id, int $qte): void
    {
        Database::query('UPDATE equipements SET stock_disponible = stock_disponible + ? WHERE id = ?', [$qte, $id]);
    }

    public static function decrementerStock(int $id, int $qte): bool
    {
        return Database::query(
            'UPDATE equipements SET stock_disponible = stock_disponible - ?
             WHERE id = ? AND stock_disponible >= ?',
            [$qte, $id, $qte]
        )->rowCount() > 0;
    }

    // ------- recherche multicritères -------

    public static function search(array $filters): array
    {
        $sql = 'SELECT e.*, c.nom AS categorie_nom
                FROM equipements e
                INNER JOIN categories c ON c.id = e.categorie_id
                WHERE 1=1';
        $params = [];

        if (!empty($filters['q'])) {
            $sql .= ' AND (e.nom LIKE :q1 OR e.marque LIKE :q2 OR e.modele LIKE :q3
                          OR e.description LIKE :q4 OR c.nom LIKE :q5)';
            $like = '%' . $filters['q'] . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
            $params[':q4'] = $like;
            $params[':q5'] = $like;
        }
        if (!empty($filters['categorie_id'])) {
            $sql .= ' AND e.categorie_id = :cat';
            $params[':cat'] = $filters['categorie_id'];
        }
        if (isset($filters['prix_min']) && $filters['prix_min'] !== '') {
            $sql .= ' AND e.prix_jour >= :pmin';
            $params[':pmin'] = $filters['prix_min'];
        }
        if (isset($filters['prix_max']) && $filters['prix_max'] !== '') {
            $sql .= ' AND e.prix_jour <= :pmax';
            $params[':pmax'] = $filters['prix_max'];
        }
        if (isset($filters['disponible']) && $filters['disponible'] !== '') {
            if ($filters['disponible'] === 'oui') {
                $sql .= ' AND e.stock_disponible > 0 AND e.etat = "disponible"';
            } else {
                $sql .= ' AND (e.stock_disponible <= 0 OR e.etat <> "disponible")';
            }
        }
        if (isset($filters['alerte']) && $filters['alerte'] !== '') {
            if ($filters['alerte'] === 'oui') {
                $sql .= ' AND e.stock_disponible <= e.seuil_alerte';
            } else {
                $sql .= ' AND e.stock_disponible > e.seuil_alerte';
            }
        }
        if (!empty($filters['etat'])) {
            $sql .= ' AND e.etat = :etat';
            $params[':etat'] = $filters['etat'];
        }

        $orderMap = [
            'nom_asc'  => 'e.nom ASC',
            'nom_desc' => 'e.nom DESC',
            'prix_asc' => 'e.prix_jour ASC',
            'prix_desc'=> 'e.prix_jour DESC',
            'stock_asc'=> 'e.stock_disponible ASC',
            'stock_desc'=> 'e.stock_disponible DESC',
        ];
        $order = $orderMap[$filters['tri'] ?? ''] ?? 'e.nom ASC';
        $sql .= ' ORDER BY ' . $order;

        return Database::fetchAll($sql, $params);
    }

    // ------- stats -------

    public static function count(): int
    {
        return (int) Database::fetchColumn('SELECT COUNT(*) FROM equipements');
    }

    public static function countDisponibles(): int
    {
        return (int) Database::fetchColumn('SELECT COUNT(*) FROM equipements WHERE stock_disponible > 0 AND etat = "disponible"');
    }

    // Nombre d'équipements distincts loués par un client (hors annulations)
    public static function countRentedByClient(int $clientId): int
    {
        return (int) Database::fetchColumn(
            'SELECT COUNT(DISTINCT e.id)
             FROM equipements e
             INNER JOIN locations l ON l.equipement_id = e.id
             WHERE l.client_id = ? AND l.statut <> "annulee"',
            [$clientId]
        );
    }

    // Parmi les équipements loués par le client, combien sont actuellement disponibles
    public static function countDisponiblesByClient(int $clientId): int
    {
        return (int) Database::fetchColumn(
            'SELECT COUNT(DISTINCT e.id)
             FROM equipements e
             INNER JOIN locations l ON l.equipement_id = e.id
             WHERE l.client_id = ? AND l.statut <> "annulee" AND e.stock_disponible > 0',
            [$clientId]
        );
    }

    public static function stockTotal(): int
    {
        return (int) Database::fetchColumn('SELECT IFNULL(SUM(stock_disponible), 0) FROM equipements');
    }

    // équipements sous le seuil d'alerte
    public static function enAlerte(): array
    {
        return Database::fetchAll(
            'SELECT e.*, c.nom AS categorie_nom
             FROM equipements e
             INNER JOIN categories c ON c.id = e.categorie_id
             WHERE e.stock_disponible <= e.seuil_alerte
             ORDER BY e.stock_disponible ASC'
        );
    }

    // équipements revenus endommagés, à réparer ou remplacer
    public static function endommages(): array
    {
        return Database::fetchAll(
            'SELECT e.*, c.nom AS categorie_nom
             FROM equipements e
             INNER JOIN categories c ON c.id = e.categorie_id
             WHERE e.etat = "endommage"
             ORDER BY e.nom ASC'
        );
    }

    // remet en service un équipement endommagé après réparation
    public static function reparer(int $id): bool
    {
        return Database::query(
            'UPDATE equipements SET etat = "disponible" WHERE id = ? AND etat = "endommage"',
            [$id]
        )->rowCount() > 0;
    }

    // remplace un équipement endommagé (nouvel exemplaire remis en stock)
    public static function remplacer(int $id): bool
    {
        return Database::query(
            'UPDATE equipements SET etat = "disponible" WHERE id = ? AND etat = "endommage"',
            [$id]
        )->rowCount() > 0;
    }

    // les plus loués (pour le dashboard)
    public static function plusLoues(int $limit = 5): array
    {
        return Database::fetchAll(
            'SELECT e.*, c.nom AS categorie_nom, COUNT(l.id) AS nb_locations,
                    IFNULL(SUM(l.montant_total), 0) AS revenus
             FROM equipements e
             INNER JOIN categories c ON c.id = e.categorie_id
             LEFT JOIN locations l ON l.equipement_id = e.id AND l.statut <> "annulee"
             GROUP BY e.id
             ORDER BY nb_locations DESC
             LIMIT ' . (int) $limit
        );
    }

    // équipements en promo
    public static function enPromo(int $limit = 4): array
    {
        return Database::fetchAll(
            'SELECT e.*, c.nom AS categorie_nom
             FROM equipements e
             INNER JOIN categories c ON c.id = e.categorie_id
             WHERE e.en_promo = 1
               AND e.prix_promo IS NOT NULL
               AND e.prix_promo > 0
               AND e.prix_promo < e.prix_jour
             ORDER BY (e.prix_jour - e.prix_promo) DESC
             LIMIT ' . (int) $limit
        );
    }
}
