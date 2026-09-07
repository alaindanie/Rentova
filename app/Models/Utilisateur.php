<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Auth;
use App\Core\Session;

/**
 * Modèle Utilisateur — CRUD + authentification + statistiques.
 */
class Utilisateur
{
    public static function table(): string
    {
        return 'utilisateurs';
    }

    public static function all(string $order = 'cree_le DESC'): array
    {
        return Database::fetchAll("SELECT * FROM utilisateurs ORDER BY {$order}");
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM utilisateurs WHERE id = ?', [$id]);
    }

    public static function findByEmail(string $email): ?array
    {
        return Database::fetch('SELECT * FROM utilisateurs WHERE email = ?', [$email]);
    }

    /** Liste des clients (pour saisir une demande au nom d'un client). */
    public static function clients(): array
    {
        return Database::fetchAll(
            "SELECT id, nom, prenom, email, telephone FROM utilisateurs
             WHERE role = 'client'
             ORDER BY nom ASC, prenom ASC"
        );
    }

    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO utilisateurs (nom, prenom, email, telephone, adresse, password, role, photo)
             VALUES (:nom, :prenom, :email, :telephone, :adresse, :password, :role, :photo)',
            [
                ':nom'       => $data['nom'],
                ':prenom'    => $data['prenom'],
                ':email'     => $data['email'],
                ':telephone' => $data['telephone'] ?? null,
                ':adresse'   => $data['adresse'] ?? null,
                ':password'  => password_hash($data['password'], PASSWORD_DEFAULT),
                ':role'      => $data['role'] ?? 'client',
                ':photo'     => $data['photo'] ?? null,
            ]
        );
    }

    public static function update(int $id, array $data): bool
    {
        $sql = 'UPDATE utilisateurs SET
                    nom = :nom, prenom = :prenom, email = :email,
                    telephone = :telephone, adresse = :adresse, role = :role
                    ' . (!empty($data['password']) ? ', password = :password' : '') . '
                    ' . (array_key_exists('photo', $data) && $data['photo'] !== null ? ', photo = :photo' : '') . '
                WHERE id = :id';
        $params = [
            ':nom'       => $data['nom'],
            ':prenom'    => $data['prenom'],
            ':email'     => $data['email'],
            ':telephone' => $data['telephone'] ?? null,
            ':adresse'   => $data['adresse'] ?? null,
            ':role'      => $data['role'] ?? 'client',
            ':id'        => $id,
        ];
        if (!empty($data['password'])) {
            $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (array_key_exists('photo', $data) && $data['photo'] !== null) {
            $params[':photo'] = $data['photo'];
        }
        return Database::query($sql, $params)->rowCount() >= 0;
    }

    public static function delete(int $id): bool
    {
        return Database::query('DELETE FROM utilisateurs WHERE id = ?', [$id])->rowCount() > 0;
    }

    /** Tente de connecter l'utilisateur, retourne l'utilisateur ou null. */
    public static function attempt(string $email, string $password): ?array
    {
        $user = self::findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }
        return null;
    }

    /** Recharge l'utilisateur connecté dans la session. */
    public static function touchSession(int $id): void
    {
        $user = self::find($id);
        if ($user) {
            unset($user['password']);
            if (Auth::check() && (int) ($_SESSION['user']['id'] ?? 0) === $id) {
                Session::set('user', $user);
            }
        }
    }

    /* --------------------------- Statistiques --------------------------- */

    public static function count(): int
    {
        return (int) Database::fetchColumn('SELECT COUNT(*) FROM utilisateurs');
    }

    public static function countByRole(string $role): int
    {
        return (int) Database::fetchColumn('SELECT COUNT(*) FROM utilisateurs WHERE role = ?', [$role]);
    }

    /** Clients ayant le plus de locations (jointure). */
    public static function topClients(int $limit = 5): array
    {
        return Database::fetchAll(
            'SELECT u.id, u.nom, u.prenom, u.email, u.photo,
                    COUNT(l.id) AS nb_locations,
                    IFNULL(SUM(l.montant_total), 0) AS total_depense
             FROM utilisateurs u
             LEFT JOIN locations l ON l.client_id = u.id
             WHERE u.role = "client"
             GROUP BY u.id
             ORDER BY nb_locations DESC, total_depense DESC
             LIMIT ' . (int) $limit
        );
    }
}
