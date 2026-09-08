<?php
namespace App\Models;

/**
 * Docly - Modèle de Base
 * Fournit les opérations CRUD de base
 */
abstract class Model {
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [];
    protected static array $casts = [];

    protected static function db(): \PDO {
        return $GLOBALS['db'];
    }

    /**
     * Trouve un enregistrement par ID
     */
    public static function find(int $id): ?array {
        $stmt = self::db()->prepare("SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Récupère tous les enregistrements
     */
    public static function all(string $orderBy = 'id DESC'): array {
        $stmt = self::db()->query("SELECT * FROM " . static::$table . " ORDER BY $orderBy");
        return $stmt->fetchAll();
    }

    /**
     * Récupère avec une clause WHERE
     */
    public static function where(string $column, $value, string $operator = '='): array {
        $stmt = self::db()->prepare("SELECT * FROM " . static::$table . " WHERE $column $operator :value");
        $stmt->execute([':value' => $value]);
        return $stmt->fetchAll();
    }

    /**
     * Première correspondance
     */
    public static function firstWhere(string $column, $value, string $operator = '='): ?array {
        $stmt = self::db()->prepare("SELECT * FROM " . static::$table . " WHERE $column $operator :value LIMIT 1");
        $stmt->execute([':value' => $value]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Crée un enregistrement
     */
    public static function create(array $data): int {
        $filtered = array_intersect_key($data, array_flip(static::$fillable));

        // Les champs facultatifs non renseignés arrivent ici avec une valeur
        // null (voir Controller::sanitize()). On les retire complètement de
        // l'INSERT plutôt que d'écrire explicitement NULL : certaines
        // colonnes ont une valeur DEFAULT en base (ex: blood_type DEFAULT
        // 'unknown', is_active DEFAULT 1) qui ne s'applique QUE si la
        // colonne est absente de la requête, jamais si on lui passe NULL
        // explicitement.
        $filtered = array_filter($filtered, fn($value) => $value !== null);

        $columns = implode(', ', array_keys($filtered));
        $placeholders = ':' . implode(', :', array_keys($filtered));

        $stmt = self::db()->prepare("INSERT INTO " . static::$table . " ($columns) VALUES ($placeholders)");
        $stmt->execute($filtered);

        return (int) self::db()->lastInsertId();
    }

    /**
     * Met à jour un enregistrement
     */
    public static function update(int $id, array $data): bool {
        $filtered = array_intersect_key($data, array_flip(static::$fillable));
        $sets = [];
        foreach ($filtered as $key => $value) {
            $sets[] = "$key = :$key";
        }
        $setString = implode(', ', $sets);

        $stmt = self::db()->prepare("UPDATE " . static::$table . " SET $setString WHERE " . static::$primaryKey . " = :id");
        $filtered[':id'] = $id;

        return $stmt->execute($filtered);
    }

    /**
     * Supprime un enregistrement (soft delete si colonne deleted_at existe)
     */
    public static function delete(int $id): bool {
        $stmt = self::db()->prepare("UPDATE " . static::$table . " SET deleted_at = NOW() WHERE " . static::$primaryKey . " = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Suppression définitive
     */
    public static function forceDelete(int $id): bool {
        $stmt = self::db()->prepare("DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Compte les enregistrements
     */
    public static function count(?string $where = null, array $params = []): int {
        $sql = "SELECT COUNT(*) as count FROM " . static::$table;
        if ($where) {
            $sql .= " WHERE $where";
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetch()['count'];
    }

    /**
     * Pagination
     */
    public static function paginate(int $page = 1, int $perPage = 20, string $orderBy = 'id DESC', ?string $where = null, array $params = []): array {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM " . static::$table;
        if ($where) {
            $sql .= " WHERE $where";
        }
        $sql .= " ORDER BY $orderBy LIMIT :limit OFFSET :offset";

        $stmt = self::db()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $countSql = "SELECT COUNT(*) as count FROM " . static::$table;
        if ($where) {
            $countSql .= " WHERE $where";
        }
        $countStmt = self::db()->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetch()['count'];

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Recherche fulltext simple
     */
    public static function search(string $query, array $columns, int $limit = 20): array {
        $conditions = [];
        $params = [];
        foreach ($columns as $i => $column) {
            $conditions[] = "$column LIKE :q$i";
            $params[":q$i"] = "%$query%";
        }
        $where = implode(' OR ', $conditions);
        $stmt = self::db()->prepare("SELECT * FROM " . static::$table . " WHERE ($where) LIMIT :limit");
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
