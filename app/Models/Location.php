<?php

namespace App\Models;

use App\Core\Database;

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

    // génération de la référence LOC-XXXX
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

    // ------- disponibilité -------

    // vérifie qu'on peut louer une quantité sur une période donnée
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

    // ------- flux de location -------

    // confirme une demande : on décrémente le stock et on démarre immédiatement la location
    public static function confirmer(int $id, int $agentId): bool
    {
        $location = self::find($id);
        if (!$location) {
            return false;
        }
        $equipement = Equipement::find($location['equipement_id']);
        /* Un équipement endommagé ou en maintenance ne peut pas être reloué. */
        if (!$equipement || $equipement['etat'] !== 'disponible') {
            return false;
        }
        $pdo = Database::db();
        try {
            $pdo->beginTransaction();
            if (!Equipement::decrementerStock($location['equipement_id'], (int) $location['quantite'])) {
                $pdo->rollBack();
                return false;
            }
            /* L'équipement passe à l'état « en location » dès la confirmation. */
            Database::query(
                'UPDATE equipements SET etat = "en_location" WHERE id = :id AND etat = "disponible"',
                [':id' => $location['equipement_id']]
            );
            /* La confirmation démarre directement la location : statut « en_cours ». */
            self::updateStatut($id, 'en_cours', $agentId);
            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    // passe en cours
    public static function demarrer(int $id): bool
    {
        return self::updateStatut($id, 'en_cours');
    }

    // retour d'un équipement
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

            /* L'état de l'équipement est recalculé : endommagé si dégradé, sinon disponible. */
            if ($data['condition_retour'] === 'endommage') {
                Database::query(
                    'UPDATE equipements SET etat = "endommage" WHERE id = :id',
                    [':id' => $location['equipement_id']]
                );
            } else {
                Database::query(
                    'UPDATE equipements SET etat = "disponible" WHERE id = :id AND etat = "en_location"',
                    [':id' => $location['equipement_id']]
                );
            }
            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    // annulation : on remet le stock
    public static function annuler(int $id, string $raison = ''): bool
    {
        $location = self::find($id);
        if (!$location) {
            return false;
        }
        if (in_array($location['statut'], ['confirmee', 'en_cours'], true)) {
            Equipement::incrementerStock($location['equipement_id'], (int) $location['quantite']);
            /* Stock libéré : l'équipement redevient disponible. */
            Database::query(
                'UPDATE equipements SET etat = "disponible" WHERE id = :id AND etat = "en_location"',
                [':id' => $location['equipement_id']]
            );
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

    // ------- stats -------

    public static function count(?string $statut = null): int
    {
        if ($statut) {
            return (int) Database::fetchColumn('SELECT COUNT(*) FROM locations WHERE statut = ?', [$statut]);
        }
        return (int) Database::fetchColumn('SELECT COUNT(*) FROM locations');
    }

    // Nombre de locations d'un client (hors annulations)
    public static function countByClient(int $clientId, ?string $statut = null): int
    {
        if ($statut) {
            return (int) Database::fetchColumn(
                'SELECT COUNT(*) FROM locations WHERE client_id = ? AND statut = ?',
                [$clientId, $statut]
            );
        }
        return (int) Database::fetchColumn(
            'SELECT COUNT(*) FROM locations WHERE client_id = ? AND statut <> "annulee"',
            [$clientId]
        );
    }

    // Locations d'un client actuellement en cours ou confirmées sur la période du jour
    public static function countEnCoursByClient(int $clientId): int
    {
        $today = date('Y-m-d');
        return (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM locations
             WHERE client_id = ?
               AND (statut = 'en_cours'
                    OR (statut = 'confirmee' AND date_debut <= ? AND date_fin >= ?))",
            [$clientId, $today, $today]
        );
    }

    // Chiffre d'affaires généré par les locations d'un client (sa dépense)
    public static function chiffreAffairesByClient(int $clientId, bool $avecFrais = true): float
    {
        $frais = $avecFrais ? ' + IFNULL(montant_frais, 0)' : '';
        return (float) Database::fetchColumn(
            "SELECT IFNULL(SUM(montant_base{$frais}), 0)
             FROM locations
             WHERE client_id = ? AND statut IN ('confirmee', 'en_cours', 'terminee')",
            [$clientId]
        );
    }

    public static function chiffreAffaires(bool $avecFrais = true): float
    {
        $frais = $avecFrais ? ' + IFNULL(montant_frais, 0)' : '';
        return (float) Database::fetchColumn(
            'SELECT IFNULL(SUM(montant_base' . $frais . '), 0) FROM locations WHERE statut IN ("confirmee", "en_cours", "terminee")'
        );
    }

    // locations en cours actuellement
    public static function enCoursMaintenant(): array
    {
        $today = date('Y-m-d');
        return self::all(
            '(l.statut = "en_cours")
             OR (l.statut = "confirmee" AND l.date_debut <= :today AND l.date_fin >= :today)',
            [':today' => $today]
        );
    }

    // retours prévus dans les prochains jours
    public static function retoursImminents(int $jours = 3): array
    {
        $limit = date('Y-m-d', strtotime("+{$jours} days"));
        return self::all(
            "l.statut = 'en_cours' AND l.date_fin <= :limit AND l.date_fin >= :today",
            [':limit' => $limit, ':today' => date('Y-m-d')]
        );
    }

    public static function countEnRetard(): int
    {
        return (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM locations
             WHERE statut = 'en_cours'
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

    // Activité mensuelle d'un client (hors annulations)
    public static function statsMensuellesByClient(int $clientId, int $months = 6): array
    {
        return Database::fetchAll(
            "SELECT DATE_FORMAT(l.cree_le, '%Y-%m') AS mois,
                    COUNT(*) AS nb,
                    IFNULL(SUM(l.montant_total), 0) AS montant
             FROM locations l
             WHERE l.client_id = :client
               AND l.statut <> 'annulee'
               AND l.cree_le >= DATE_SUB(CURDATE(), INTERVAL " . (int) $months . " MONTH)
             GROUP BY mois
             ORDER BY mois ASC",
            [':client' => $clientId]
        );
    }
}
