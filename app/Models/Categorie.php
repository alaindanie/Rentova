<?php

namespace App\Models;

use App\Core\Database;

/**
 * Modèle Catégorie Équipement — CRUD complet.
 */
class Categorie
{
    public static function all(): array
    {
        return Database::fetchAll('SELECT * FROM categories ORDER BY nom ASC');
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM categories WHERE id = ?', [$id]);
    }

    public static function findByNom(string $nom): ?array
    {
        return Database::fetch('SELECT * FROM categories WHERE nom = ?', [$nom]);
    }

    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO categories (nom, description, image) VALUES (:nom, :description, :image)',
            [
                ':nom'         => $data['nom'],
                ':description' => $data['description'] ?? null,
                ':image'       => $data['image'] ?? null,
            ]
        );
    }

    public static function update(int $id, array $data): bool
    {
        return Database::query(
            'UPDATE categories SET nom = :nom, description = :description, image = :image WHERE id = :id',
            [
                ':nom'         => $data['nom'],
                ':description' => $data['description'] ?? null,
                ':image'       => $data['image'] ?? null,
                ':id'          => $id,
            ]
        )->rowCount() >= 0;
    }

    public static function delete(int $id): bool
    {
        return Database::query('DELETE FROM categories WHERE id = ?', [$id])->rowCount() > 0;
    }

    public static function count(): int
    {
        return (int) Database::fetchColumn('SELECT COUNT(*) FROM categories');
    }

    /** Nombre d'équipements par catégorie (jointure avec équipements). */
    public static function countEquipements(int $id): int
    {
        return (int) Database::fetchColumn(
            'SELECT COUNT(*) FROM equipements WHERE categorie_id = ?',
            [$id]
        );
    }

    /** Catégories avec nombre d'équipements et mini-résumé (jointure). */
    public static function withStats(): array
    {
        return Database::fetchAll(
            'SELECT c.*, COUNT(e.id) AS nb_equipements,
                    IFNULL(SUM(e.stock_disponible), 0) AS stock_total_categorie
             FROM categories c
             LEFT JOIN equipements e ON e.categorie_id = c.id
             GROUP BY c.id
             ORDER BY c.nom ASC'
        );
    }
}
