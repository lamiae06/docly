<?php
/**
 * Docly - Multi-tenant (une base de données MySQL par cabinet)
 *
 * Principe : $GLOBALS['central_db'] pointe TOUJOURS vers la base centrale
 * (liste des cabinets, super-admins). $GLOBALS['db'] pointe vers la base du
 * cabinet actuellement connecté (résolue à partir de la session) — c'est
 * cette variable que App\Models\Model::db() utilise, donc tout le code
 * existant (modèles, requêtes directes) reste inchangé et ne peut PAS
 * accidentellement lire la base d'un autre cabinet : la connexion PDO
 * elle-même est différente.
 */

/**
 * Ouvre une connexion PDO vers une base précise sur le serveur configuré.
 */
function open_pdo_connection(array $dbConfig, string $databaseName): PDO {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$databaseName};charset={$dbConfig['charset']}";
    return new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
}

/**
 * Transforme un nom de cabinet en identifiant ("slug") utilisable dans une
 * URL / au login : minuscules, chiffres et tirets uniquement.
 */
function slugify(string $text): string {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text !== '' ? $text : 'cabinet';
}

/**
 * Garantit un slug unique dans la base centrale (ajoute -2, -3... si besoin).
 */
function generate_unique_cabinet_slug(PDO $centralPdo, string $name): string {
    $base = slugify($name);
    $slug = $base;
    $i = 2;
    $stmt = $centralPdo->prepare("SELECT COUNT(*) FROM cabinets WHERE slug = :slug");
    while (true) {
        $stmt->execute([':slug' => $slug]);
        if ((int) $stmt->fetchColumn() === 0) {
            return $slug;
        }
        $slug = $base . '-' . $i;
        $i++;
    }
}

/**
 * Construit un nom de base MySQL sûr à partir du slug. Le nom d'une base ne
 * peut pas être paramétré dans une requête préparée (ce n'est pas une
 * valeur, c'est un identifiant SQL) : on le construit donc nous-mêmes en ne
 * gardant QUE des caractères alphanumériques et underscore, jamais de
 * caractère fourni tel quel par l'utilisateur.
 */
function slug_to_db_name(string $slug): string {
    $safe = preg_replace('/[^a-z0-9]+/', '_', strtolower($slug));
    $safe = trim($safe, '_');
    return 'docly_cab_' . substr($safe, 0, 40);
}

/**
 * Exécute un fichier .sql "simple" (uniquement des instructions séparées par
 * des points-virgules, sans DELIMITER/procédures stockées — ce qui est le
 * cas de schema.sql et base_roles.sql). PDO n'exécute qu'une instruction à
 * la fois, on découpe donc le fichier nous-mêmes.
 */
function run_sql_file(PDO $pdo, string $path): void {
    $sql = file_get_contents($path);
    if ($sql === false) {
        throw new RuntimeException("Fichier SQL introuvable : $path");
    }
    // Retire les commentaires de ligne complète pour éviter de couper une
    // instruction au milieu à cause d'un point-virgule dans un commentaire.
    $lines = array_filter(
        explode("\n", $sql),
        fn($line) => !str_starts_with(trim($line), '--')
    );
    $sql = implode("\n", $lines);

    foreach (explode(';', $sql) as $statement) {
        $statement = trim($statement);
        if ($statement === '') continue;
        $pdo->exec($statement);
    }
}

/**
 * Crée une nouvelle base de données pour un cabinet et la peuple avec le
 * schéma applicatif + les rôles/permissions de base. Retourne le nom de
 * base créé.
 */
function provision_cabinet_database(PDO $centralPdo, array $dbConfig, string $dbName): void {
    // Le nom de la base est déjà assaini par slug_to_db_name() (uniquement
    // [a-z0-9_]), donc l'interpoler directement dans le DDL est sûr : on ne
    // peut pas paramétrer un nom de base dans une requête préparée.
    $centralPdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $tenantPdo = open_pdo_connection($dbConfig, $dbName);
    run_sql_file($tenantPdo, BASE_PATH . '/database/migrations/schema.sql');
    run_sql_file($tenantPdo, BASE_PATH . '/database/seeders/base_roles.sql');
}
