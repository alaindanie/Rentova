<?php

namespace App\Models;

use App\Core\Database;

/**
 * Modèle Location — CRUD + validations de disponibilité + retours + facturation.
 */
class Location
{
    public static function find(int $id): ?array
    {
        return Database::fetch(
            'SELECT l.*,
                    e.nom AS equipement_nom, e.marque, e.modele, e.image AS equipement_image,
                    e.prix_jour AS prix_jour, c.nom AS categorie_nom,
                    u.nom AS client_nom, u.prenom AS client_prenom, u.email AS client_email,
                    u.telephone AS client_telephone, u.adresse AS client_adresse,
                    ag.nom AS agent_nom, ag.prenom AS agent_prenom
             FROM locations l
             INNER JOIN equipements e      ON e.id = l.equipement_id
             INNER JOIN categories c       ON c.id = e.categorie_id
             INNER JOIN utilisateurs u     ON u.id = l.client_id
             LEFT JOIN utilisateurs ag     ON ag.id = l.agent_id
             WHERE l.id = ?',
            [$id]
        );
    }

    public static function findByReference(string $reference): ?array
    {
        return Database::fetch(
            'SELECT l.*, e.nom AS equipement_nom
             FROM locations l
             INNER JOIN equipements e ON e.id = l.equipement_id
             WHERE l.reference = ?',
            [$reference]
        );
    }

    public static function all(?string $where = '', array $params = []): array
    {
        $sql = 'SELECT l.*,
                    e.nom AS equipement_nom, e.image AS equipement_image,
                    c.nom AS categorie_nom,
                    u.nom AS client_nom, u.prenom AS client_prenom, u.email AS client_email
                FROM locations l
                INNER JOIN equipements e ON e.id = l.equipement_id
                INNER JOIN categories c  ON c.id = e.categorie_id
                INNER JOIN utilisateurs u ON u.id = l.client_id';
        if ($where) {
            $sql .= ' WHERE ' . $where;
        }
        $sql .= ' ORDER BY l.cree_le DESC, l.id DESC';
        return Database::fetchAll($sql, $params);
    }

    public static function ofClient(int $clientId): array
    {
        return self::all('l.client_id = ?', [$clientId]);
    }

    public static function ofEquipement(int $equipementId): array
    {
        return self::all('l.equipement_id = ?', [$equipementId]);
    }

    /* --------------------------- Génération de référence --------------------------- */

    public static function generateReference(): string
    {
        $prefix = 'LOC-' . date('ym');
        $last = Database::fetchColumn(
            "SELECT reference FROM locations
             WHERE reference LIKE :prefix
             ORDER BY id DESC LIMIT 1",
            [':prefix' => $prefix . '%']
        );
        $next = 1;
        if ($last) {
            $num = (int) substr($last, -4);
            $next = $num + 1;
        }
        return $prefix . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public static function create(array $data): int
    {
        $duree = max(1, (int) diff_days($data['date_debut'], $data['date_fin']));
        $montant = round((float) $data['montant_base'], 2);

        return Database::insert(
            'INSERT INTO locations
                (reference, client_id, equipement_id, agent_id,
                 date_debut, date_fin, duree, quantite,
                 montant_base, montant_total, statut, note)
             VALUES
                (:reference, :client_id, :equipement_id, :agent_id,
                 :date_debut, :date_fin, :duree, :quantite,
                 :montant_base, :montant_total, :statut, :note)',
            [
                ':reference'     => $data['reference'],
                ':client_id'     => $data['client_id'],
                ':equipement_id' => $data['equipement_id'],
                ':agent_id'      => $data['agent_id'] ?? null,
                ':date_debut'    => $data['date_debut'],
                ':date_fin'      => $data['date_fin'],
                ':duree'         => $duree,
                ':quantite'      => (int) ($data['quantite'] ?? 1),
                ':montant_base'  => $montant,
                ':montant_total' => $montant + (float) ($data['montant_frais'] ?? 0),
                ':statut'        => $data['statut'] ?? 'en_attente',
                ':note'          => $data['note'] ?? null,
            ]
        );
    }

    public static function update(int $id, array $data): bool
    {
        $duree = max(1, (int) diff_days($data['date_debut'], $data['date_fin']));
        $montant = round((float) $data['montant_base'], 2);
        return Database::query(
            'UPDATE locations SET
                equipement_id = :equipement_id,
                date_debut = :date_debut, date_fin = :date_fin,
                duree = :duree, quantite = :quantite,
                montant_base = :montant_base,
                montant_total = :montant_base + :frais,
                statut = :statut, note = :note
             WHERE id = :id',
            [
                ':equipement_id' => $data['equipement_id'],
                ':date_debut'    => $data['date_debut'],
                ':date_fin'      => $data['date_fin'],
                ':duree'         => $duree,
                ':quantite'      => (int) ($data['quantite'] ?? 1),
                ':montant_base'  => $montant,
                ':frais'         => (float) ($data['montant_frais'] ?? 0),
                ':statut'        => $data['statut'] ?? 'en_attente',
                ':note'          => $data['note'] ?? null,
                ':id'            => $id,
            ]
        )->rowCount() >= 0;
    }

    public static function updateStatut(int $id, string $statut, ?int $agentId = null): bool
    {
        if ($agentId !== null) {
            return Database::query(
                'UPDATE locations SET statut = :statut, agent_id = :agent_id WHERE id = :id',
                [':statut' => $statut, ':agent_id' => $agentId, ':id' => $id]
            )->rowCount() > 0;
        }
        return Database::query(
            'UPDATE locations SET statut = :statut WHERE id = :id',
            [':statut' => $statut, ':id' => $id]
        )->rowCount() > 0;
    }

    public static function delete(int $id): bool
    {
        return Database::query('DELETE FROM locations WHERE id = ?', [$id])->rowCount() > 0;
    }

    /* --------------------------- Disponibilité --------------------------- */

    /**
     * Vérifie la disponibilité d'une quantité sur une période donnée.
     * Exclut les locations annulées/refusées et la location courante (si édition).
     */
    public static function disponibilite(int $equipementId, string $debut, string $fin, int $quantite, ?int $excludeId = null): bool
    {
        $equipement = Equipement::find($equipementId);
        if (!$equipement) {
            return false;
        }
        if ($quantite > (int) $equipement['stock_disponible']) {
            return false;
        }

        $sql = 'SELECT IFNULL(SUM(l.quantite), 0)
                FROM locations l
                WHERE l.equipement_id = :equipement_id
                  AND l.statut IN ("en_attente", "confirmee", "en_cours")
                  AND l.date_debut <= :fin
                  AND l.date_fin >= :debut';
        $params = [':equipement_id' => $equipementId, ':fin' => $fin, ':debut' => $debut];
        if ($excludeId) {
            $sql .= ' AND l.id <> :exclude';
            $params[':exclude'] = $excludeId;
        }
        $dejaReserve = (int) Database::fetchColumn($sql, $params);

        return ($dejaReserve + $quantite) <= (int) $equipement['stock_disponible'];
    }

    /* --------------------------- Flux de location --------------------------- */

    /** Confirme une demande : décrémente le stock, passe en "confirmee". */
    public static function confirmer(int $id, int $agentId): bool
    {
        $location = self::find($id);
        if (!$location) {
            return false;
        }
        $pdo = Database::db();
        try {
            $pdo->beginTransaction();
            if (!Equipement::decrementerStock($location['equipement_id'], (int) $location['quantite'])) {
                $pdo->rollBack();
                return false;
            }
            self::updateStatut($id, 'confirmee', $agentId);
            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    /** Passe une location confirmée en "en_cours" (au début de la période). */
    public static function demarrer(int $id): bool
    {
        return self::updateStatut($id, 'en_cours');
    }

    /**
     * Enregistre le retour : statut "terminee", date effective,
     * condition, frais additionnels et commentaire.
     */
    public static function enregistrerRetour(int $id, array $data): bool
    {
        $pdo = Database::db();
        try {
            $pdo->beginTransaction();
            $location = self::find($id);
            if (!$location) {
                return false;
            }

            $dateFin = $location['date_fin'];
            $dateRetour = $data['date_retour_effectif'] ?: date('Y-m-d');

            /* Frais de retard automatiques si retour après la date de fin. */
            $frais = (float) ($data['montant_frais'] ?? 0);
            if ($dateRetour > $dateFin) {
                $joursRetard = diff_days($dateFin, $dateRetour);
                $frais += $joursRetard * RETARD_PAR_JOUR * (int) $location['quantite'];
            }

            $montantTotal = (float) $location['montant_base'] + $frais;

            Database::query(
                'UPDATE locations SET
                    statut = "terminee",
                    date_retour_effectif = :date_retour,
                    condition_retour = :condition_retour,
                    commentaire_retour = :commentaire,
                    montant_frais = :frais,
                    montant_total = :montant_total
                 WHERE id = :id',
                [
                    ':date_retour'     => $dateRetour,
                    ':condition_retour'=> $data['condition_retour'] ?? 'bon',
                    ':commentaire'     => $data['commentaire'] ?? null,
                    ':frais'           => $frais,
                    ':montant_total'   => $montantTotal,
                    ':id'              => $id,
                ]
            );

            /* Le stock est réapprovisionné après un retour validé. */
            Equipement::incrementerStock($location['equipement_id'], (int) $location['quantite']);
            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    /** Annule une location confirmée : le stock est restauré. */
    public static function annuler(int $id, string $raison = ''): bool
    {
        $location = self::find($id);
        if (!$location) {
            return false;
        }
        if (in_array($location['statut'], ['confirmee', 'en_cours'], true)) {
            Equipement::incrementerStock($location['equipement_id'], (int) $location['quantite']);
        }
        if ($raison) {
            return self::updateStatut($id, 'annulee') && Database::query(
                'UPDATE locations SET note = :note WHERE id = :id',
                [':note' => $raison, ':id' => $id]
            )->rowCount() > 0;
        }
        return self::updateStatut($id, 'annulee');
    }

    public static function refuser(int $id, ?int $agentId = null): bool
    {
        return self::updateStatut($id, 'refusee', $agentId);
    }

    /* --------------------------- Statistiques --------------------------- */

    public static function count(?string $statut = null): int
    {
        if ($statut) {
            return (int) Database::fetchColumn('SELECT COUNT(*) FROM locations WHERE statut = ?', [$statut]);
        }
        return (int) Database::fetchColumn('SELECT COUNT(*) FROM locations');
    }

    public static function chiffreAffaires(bool $avecFrais = true): float
    {
        $frais = $avecFrais ? ' + IFNULL(montant_frais, 0)' : '';
        return (float) Database::fetchColumn(
            'SELECT IFNULL(SUM(montant_base' . $frais . '), 0) FROM locations WHERE statut IN ("confirmee", "en_cours", "terminee")'
        );
    }

    /** En cours : confirmées et dont la période inclut aujourd'hui, ou en_cours. */
    public static function enCoursMaintenant(): array
    {
        $today = date('Y-m-d');
        return self::all(
            '(l.statut = "en_cours")
             OR (l.statut = "confirmee" AND l.date_debut <= :today AND l.date_fin >= :today)',
            [':today' => $today]
        );
    }

    /** Locations à rendre bientôt (fin sous 3 jours, non rendues). */
    public static function retoursImminents(int $jours = 3): array
    {
        $limit = date('Y-m-d', strtotime("+{$jours} days"));
        return self::all(
            "l.statut IN ('confirmee', 'en_cours') AND l.date_fin <= :limit AND l.date_fin >= :today",
            [':limit' => $limit, ':today' => date('Y-m-d')]
        );
    }

    public static function countEnRetard(): int
    {
        return (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM locations
             WHERE statut IN ('confirmee', 'en_cours')
               AND date_fin < :today AND date_retour_effectif IS NULL",
            [':today' => date('Y-m-d')]
        );
    }

    public static function statsMensuelles(int $months = 6): array
    {
        return Database::fetchAll(
            "SELECT DATE_FORMAT(cree_le, '%Y-%m') AS mois,
                    COUNT(*) AS nb,
                    IFNULL(SUM(montant_total), 0) AS montant
             FROM locations
             WHERE cree_le >= DATE_SUB(CURDATE(), INTERVAL " . (int) $months . " MONTH)
             GROUP BY mois
             ORDER BY mois ASC"
        );
    }
}
