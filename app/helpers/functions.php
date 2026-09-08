<?php
/**
 * Docly - Helpers globaux
 */

/**
 * Génère un token CSRF
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie un token CSRF
 */
function verify_csrf_token(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Génère une URL d'asset (CSS/JS/images) en tenant compte du chemin de base
 * configuré dans APP_URL (.env). Fonctionne aussi bien à la racine
 * (http://localhost:8000, via npm run dev) que sous un sous-dossier
 * (http://localhost/docly, via Apache).
 */
function asset(string $path): string {
    static $base = null;
    if ($base === null) {
        $configuredUrl = $GLOBALS['config']['url'] ?? '';
        $base = rtrim((string) parse_url($configuredUrl, PHP_URL_PATH), '/');
    }
    return $base . '/' . ltrim($path, '/');
}


function e(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirection
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Réponse JSON
 */
function json_response(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/**
 * Formate une date
 */
function format_date(?string $date, string $format = 'd/m/Y'): string {
    if (!$date) return '-';
    return date($format, strtotime($date));
}

/**
 * Formate une heure
 */
function format_time(?string $time, string $format = 'H:i'): string {
    if (!$time) return '-';
    return date($format, strtotime($time));
}

/**
 * Liste des devises disponibles dans l'application.
 * Chaque devise a un code ISO (utilisé comme valeur stockée dans
 * clinic_settings.currency), un libellé affiché dans les formulaires,
 * et un symbole/abréviation utilisé pour l'affichage des montants.
 *
 * Pour ajouter une nouvelle devise, il suffit d'ajouter une entrée ici :
 * elle apparaîtra automatiquement dans le sélecteur des paramètres de
 * la clinique (Paramètres > Clinique) et sera utilisable partout où
 * format_money() est appelé.
 */
function currency_options(): array {
    return [
        'MAD' => ['label' => 'Dirham marocain (DH)',  'symbol' => 'DH'],
        'EUR' => ['label' => 'Euro (€)',               'symbol' => '€'],
        'USD' => ['label' => 'Dollar américain ($)',   'symbol' => '$'],
        'GBP' => ['label' => 'Livre sterling (£)',     'symbol' => '£'],
        'CHF' => ['label' => 'Franc suisse (CHF)',     'symbol' => 'CHF'],
        'CAD' => ['label' => 'Dollar canadien (CA$)',  'symbol' => 'CA$'],
        'TND' => ['label' => 'Dinar tunisien (DT)',    'symbol' => 'DT'],
        'DZD' => ['label' => 'Dinar algérien (DA)',    'symbol' => 'DA'],
        'XOF' => ['label' => 'Franc CFA (FCFA)',       'symbol' => 'FCFA'],
        'SAR' => ['label' => 'Riyal saoudien (SAR)',   'symbol' => 'SAR'],
        'AED' => ['label' => 'Dirham des Émirats (AED)', 'symbol' => 'AED'],
    ];
}

/**
 * Code de la devise actuellement configurée pour le cabinet (paramètre
 * "currency" dans clinic_settings). Mis en cache pour la durée de la
 * requête afin d'éviter une requête SQL à chaque appel de format_money().
 */
function current_currency_code(): string {
    static $code = null;
    if ($code !== null) {
        return $code;
    }
    $code = 'EUR';
    if (!empty($GLOBALS['db'])) {
        try {
            $stmt = $GLOBALS['db']->prepare("SELECT `value` FROM clinic_settings WHERE `key` = 'currency'");
            $stmt->execute();
            $value = $stmt->fetchColumn();
            if ($value) {
                $code = strtoupper($value);
            }
        } catch (\Throwable $e) {
            // La table peut ne pas exister (ex : contexte plateforme) : on garde la valeur par défaut.
        }
    }
    return $code;
}

/**
 * Symbole/abréviation à afficher pour une devise donnée (ou celle du
 * cabinet si aucun code n'est précisé).
 */
function currency_symbol(?string $currencyCode = null): string {
    $currencyCode = strtoupper($currencyCode ?? current_currency_code());
    $options = currency_options();
    return $options[$currencyCode]['symbol'] ?? $currencyCode;
}

/**
 * Formate un montant avec la devise du cabinet (paramétrable dans
 * Paramètres > Clinique), ou une devise précise si elle est fournie.
 */
function format_money(float $amount, ?string $currency = null): string {
    $symbol = $currency !== null ? currency_symbol($currency) : currency_symbol();
    return number_format($amount, 2, ',', ' ') . ' ' . $symbol;
}

/**
 * Calcule l'âge à partir d'une date de naissance
 */
function calculate_age(?string $birthDate): int {
    if (!$birthDate) return 0;
    $birth = new DateTime($birthDate);
    $today = new DateTime();
    return $birth->diff($today)->y;
}

/**
 * Génère un code patient
 */
function generate_patient_code(): string {
    $year = date('Y');
    $db = $GLOBALS['db'];
    $stmt = $db->query("SELECT COUNT(*) as count FROM patients WHERE YEAR(created_at) = $year");
    $count = $stmt->fetch()['count'] + 1;
    return sprintf('P-%s-%04d', $year, $count);
}

/**
 * Génère un numéro de facture
 */
function generate_invoice_number(): string {
    $year = date('Y');
    $db = $GLOBALS['db'];
    $stmt = $db->query("SELECT COUNT(*) as count FROM invoices WHERE YEAR(created_at) = $year");
    $count = $stmt->fetch()['count'] + 1;
    return sprintf('F-%s-%04d', $year, $count);
}

/**
 * Vérifie si l'utilisateur a une permission
 */
function can(string $permission): bool {
    if (!isset($_SESSION['user_permissions'])) {
        return false;
    }
    return in_array($permission, $_SESSION['user_permissions']);
}

/**
 * Vérifie si l'utilisateur a un rôle
 */
function has_role(string $role): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Enregistre un log d'audit
 */
function audit_log(string $action, string $module, ?string $entityType = null, ?int $entityId = null, ?string $description = null, ?array $oldValues = null, ?array $newValues = null): void {
    $db = $GLOBALS['db'];
    $stmt = $db->prepare("
        INSERT INTO audit_logs (user_id, user_name, action, module, entity_type, entity_id, description, old_values, new_values, ip_address, user_agent)
        VALUES (:user_id, :user_name, :action, :module, :entity_type, :entity_id, :description, :old_values, :new_values, :ip, :ua)
    ");
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'] ?? null,
        ':user_name' => $_SESSION['user_name'] ?? 'Système',
        ':action' => $action,
        ':module' => $module,
        ':entity_type' => $entityType,
        ':entity_id' => $entityId,
        ':description' => $description,
        ':old_values' => $oldValues ? json_encode($oldValues) : null,
        ':new_values' => $newValues ? json_encode($newValues) : null,
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? null,
    ]);
}

/**
 * Crée une notification
 */
function create_notification(int $userId, string $type, string $title, string $message, ?string $link = null): void {
    $db = $GLOBALS['db'];
    $stmt = $db->prepare("
        INSERT INTO notifications (user_id, type, title, message, link)
        VALUES (:user_id, :type, :title, :message, :link)
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':type' => $type,
        ':title' => $title,
        ':message' => $message,
        ':link' => $link,
    ]);
}

/**
 * Valide un upload de fichier
 */
function validate_upload(array $file): array {
    $errors = [];
    $maxSize = $GLOBALS['config']['upload_max_size'];
    $allowed = $GLOBALS['config']['allowed_upload_extensions'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Erreur lors du téléchargement du fichier.';
        return $errors;
    }

    if ($file['size'] > $maxSize) {
        $errors[] = 'Le fichier est trop volumineux (max ' . ($maxSize / 1024 / 1024) . 'MB).';
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        $errors[] = 'Type de fichier non autorisé. Extensions acceptées: ' . implode(', ', $allowed);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = [
        'application/pdf', 'image/jpeg', 'image/png', 
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    if (!in_array($mime, $allowedMimes)) {
        $errors[] = 'Type MIME non autorisé.';
    }

    return $errors;
}

/**
 * Génère un nom de fichier sécurisé
 */
function secure_filename(string $originalName): string {
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    return bin2hex(random_bytes(16)) . '.' . strtolower($ext);
}

/**
 * Tronque un texte
 */
function truncate(string $text, int $length = 100): string {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

/**
 * Date complète en français (ex : "vendredi 04 septembre 2026"), sans
 * dépendre de setlocale()/intl qui ne sont pas garantis sur tous les serveurs.
 */
function format_date_fr(?string $timestamp = null): string {
    $ts = $timestamp ? strtotime($timestamp) : time();
    $days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    $months = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    return $days[(int) date('w', $ts)] . ' ' . date('d', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

/**
 * Nom du jour de la semaine en français à partir de son index MySQL (0=dimanche .. 6=samedi).
 */
function day_of_week_name(int $day): string {
    $days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    return $days[$day] ?? '';
}

/**
 * Badge de statut pour rendez-vous
 */
function appointment_status_badge(string $status): string {
    $badges = [
        'scheduled' => '<span class="badge badge-blue">Programmé</span>',
        'confirmed' => '<span class="badge badge-green">Confirmé</span>',
        'in_progress' => '<span class="badge badge-amber">En cours</span>',
        'completed' => '<span class="badge badge-gray">Terminé</span>',
        'cancelled' => '<span class="badge badge-danger">Annulé</span>',
        'no_show' => '<span class="badge badge-gray">Absent</span>',
    ];
    return $badges[$status] ?? '<span class="badge badge-gray">' . e($status) . '</span>';
}

/**
 * Badge de statut pour facture
 */
function invoice_status_badge(string $status): string {
    $badges = [
        'pending' => '<span class="badge badge-gray">En attente</span>',
        'paid' => '<span class="badge badge-green">Payée</span>',
        'partially_paid' => '<span class="badge badge-amber">Partiellement payée</span>',
        'overdue' => '<span class="badge badge-danger">En retard</span>',
        'cancelled' => '<span class="badge badge-gray">Annulée</span>',
    ];
    return $badges[$status] ?? '<span class="badge badge-gray">' . e($status) . '</span>';
}

/**
 * Badge de statut pour analyse
 */
function lab_status_badge(string $status): string {
    $badges = [
        'ordered' => '<span class="badge badge-blue">Demandée</span>',
        'in_progress' => '<span class="badge badge-amber">En cours</span>',
        'completed' => '<span class="badge badge-green">Terminée</span>',
        'abnormal' => '<span class="badge badge-amber">Anormale</span>',
        'critical' => '<span class="badge badge-danger">Critique</span>',
    ];
    return $badges[$status] ?? '<span class="badge badge-gray">' . e($status) . '</span>';
}

/**
 * Couleur du groupe sanguin
 */
function blood_type_color(?string $type): string {
    $colors = [
        'A+' => '#ef4444', 'A-' => '#f97316',
        'B+' => '#3b82f6', 'B-' => '#06b6d4',
        'AB+' => '#8b5cf6', 'AB-' => '#a855f7',
        'O+' => '#10b981', 'O-' => '#14b8a6',
    ];
    return $colors[$type ?? ''] ?? '#6b7280';
}
