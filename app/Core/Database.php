<?php

namespace App\Core;

/**
 * Connexion unique à la base de données via PDO.
 * Pattern Singleton — l'application n'utilise QUE PDO (aucun MySQLi).
 */
class Database
{
    private static ?Database $instance = null;
    private \PDO $connection;

    private function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET,
        ];
        $this->connection = new \PDO($dsn, DB_USER, DB_PASS, $options);
    }

    public static function instance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function pdo(): \PDO
    {
        return $this->connection;
    }

    /**
     * Retourne la connexion PDO (compatibilité/rapidité d'accès).
     */
    public static function db(): \PDO
    {
        return self::instance()->connection;
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function fetchColumn(string $sql, array $params = []): mixed
    {
        return self::query($sql, $params)->fetchColumn();
    }

    public static function insert(string $sql, array $params = []): int
    {
        self::query($sql, $params);
        return (int) self::db()->lastInsertId();
    }
}
