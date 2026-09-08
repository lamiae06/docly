<?php
namespace App\Controllers;

use App\Models\Patient;

class DocumentController extends Controller {

    private const ALLOWED_TYPES = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    ];
    private const MAX_SIZE = 10 * 1024 * 1024; // 10 Mo

    public function index(): void {
        $db = $GLOBALS['db'];
        $stmt = $db->query("
            SELECT d.*, p.first_name as patient_first_name, p.last_name as patient_last_name
            FROM documents d
            LEFT JOIN patients p ON d.patient_id = p.id
            ORDER BY d.created_at DESC
            LIMIT 50
        ");
        $this->view('documents.index', ['documents' => $stmt->fetchAll(), 'pageTitle' => 'Documents']);
    }

    public function create(): void {
        $patients = Patient::searchPatients('', 1, 200)['data'];
        $this->view('documents.create', [
            'patients' => $patients,
            'pageTitle' => 'Nouveau document',
        ]);
    }

    /**
     * Détecte le type MIME d'un fichier téléversé sans dépendre de
     * l'extension PHP "fileinfo", qui n'est pas toujours installée sur le
     * serveur (c'est ce qui causait "Call to undefined function
     * mime_content_type()"). On vérifie d'abord la signature binaire du
     * fichier (fiable, ne dépend d'aucune extension), puis on retombe sur
     * finfo/mime_content_type si présents, puis sur le type envoyé par le
     * navigateur, et enfin sur l'extension du nom de fichier.
     */
    private function detectMimeType(string $tmpPath, string $originalName, string $browserType): string {
        $handle = @fopen($tmpPath, 'rb');
        if ($handle) {
            $header = fread($handle, 8);
            fclose($handle);
            if ($header !== false) {
                if (str_starts_with($header, "%PDF")) return 'application/pdf';
                if (str_starts_with($header, "\xFF\xD8\xFF")) return 'image/jpeg';
                if (str_starts_with($header, "\x89PNG\r\n\x1a\n")) return 'image/png';
                if (str_starts_with($header, "\xD0\xCF\x11\xE0")) return 'application/msword'; // .doc (OLE2)
                if (str_starts_with($header, "PK\x03\x04")) {
                    // .docx est une archive ZIP : la signature seule ne suffit pas à la
                    // distinguer d'un .zip/.xlsx quelconque, on se fie alors à l'extension déclarée.
                    if (str_ends_with(strtolower($originalName), '.docx')) {
                        return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                    }
                }
            }
        }

        if (function_exists('finfo_open')) {
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $detected = @finfo_file($finfo, $tmpPath);
                finfo_close($finfo);
                if ($detected && isset(self::ALLOWED_TYPES[$detected])) return $detected;
            }
        }
        if (function_exists('mime_content_type')) {
            $detected = @mime_content_type($tmpPath);
            if ($detected && isset(self::ALLOWED_TYPES[$detected])) return $detected;
        }

        if ($browserType && isset(self::ALLOWED_TYPES[$browserType])) {
            return $browserType;
        }

        // Dernier recours : l'extension du nom de fichier d'origine.
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $extToMime = array_flip(self::ALLOWED_TYPES);
        // 'jpeg' et 'jpg' doivent tous les deux pointer vers image/jpeg.
        $extToMime['jpeg'] = 'image/jpeg';
        return $extToMime[$ext] ?? '';
    }

    public function store(): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $title = trim(strip_tags($_POST['title'] ?? ''));
        $description = trim(strip_tags($_POST['description'] ?? ''));
        $category = $_POST['category'] ?? 'other';
        $patientId = !empty($_POST['patient_id']) ? (int) $_POST['patient_id'] : null;

        $validCategories = ['prescription', 'lab_result', 'medical_report', 'administrative', 'imaging', 'consent', 'other'];
        if (!in_array($category, $validCategories, true)) $category = 'other';

        if ($title === '') {
            $this->json(['error' => 'Validation échouée', 'errors' => ['title' => 'Le titre est obligatoire.']], 422);
        }
        if ($patientId && !Patient::find($patientId)) {
            $this->json(['error' => 'Validation échouée', 'errors' => ['patient_id' => 'Patient invalide.']], 422);
        }
        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $errorCode = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
            if (in_array($errorCode, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
                $this->json(['error' => 'Le fichier dépasse la taille autorisée par le serveur PHP (upload_max_filesize / post_max_size dans php.ini).'], 422);
            }
            $this->json(['error' => 'Veuillez sélectionner un fichier valide.'], 422);
        }

        $file = $_FILES['file'];
        if ($file['size'] > self::MAX_SIZE) {
            $this->json(['error' => 'Le fichier dépasse la taille maximale autorisée (10 Mo).'], 422);
        }

        $mimeType = $this->detectMimeType($file['tmp_name'], $file['name'], $file['type'] ?? '');
        if (!isset(self::ALLOWED_TYPES[$mimeType])) {
            $this->json(['error' => 'Type de fichier non autorisé. Formats acceptés : PDF, JPG, PNG, DOC, DOCX.'], 422);
        }

        $extension = self::ALLOWED_TYPES[$mimeType];
        // Nom de fichier non devinable : les documents médicaux sont servis
        // statiquement par le serveur web, un nom aléatoire évite qu'on
        // puisse deviner/énumérer les fichiers d'un autre patient.
        $storedName = bin2hex(random_bytes(16)) . '.' . $extension;
        $uploadDir = BASE_PATH . '/public/uploads/documents';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $destination = $uploadDir . '/' . $storedName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->json(['error' => 'Erreur lors de l\'enregistrement du fichier.'], 500);
        }

        $db = $GLOBALS['db'];
        $stmt = $db->prepare("
            INSERT INTO documents (patient_id, title, description, category, file_path, file_name, file_size, mime_type, uploaded_by)
            VALUES (:patient_id, :title, :description, :category, :file_path, :file_name, :file_size, :mime_type, :uploaded_by)
        ");
        $stmt->execute([
            ':patient_id' => $patientId,
            ':title' => $title,
            ':description' => $description ?: null,
            ':category' => $category,
            ':file_path' => '/uploads/documents/' . $storedName,
            ':file_name' => $file['name'],
            ':file_size' => $file['size'],
            ':mime_type' => $mimeType,
            ':uploaded_by' => $_SESSION['user_id'] ?? null,
        ]);
        $id = (int) $db->lastInsertId();

        audit_log('create', 'documents', 'document', $id, 'Document ajouté : ' . $title);

        $this->respondSuccess(['id' => $id], '/documents');
    }

    public function edit(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM documents WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $document = $stmt->fetch();
        if (!$document) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $patients = Patient::searchPatients('', 1, 200)['data'];
        $this->view('documents.edit', [
            'document' => $document,
            'patients' => $patients,
            'pageTitle' => 'Modifier le document',
        ]);
    }

    /**
     * Modifie les métadonnées d'un document (titre, description, catégorie,
     * patient associé). Le fichier lui-même n'est pas remplacé ici — pour
     * changer le fichier, supprimer puis re-téléverser un nouveau document.
     */
    public function update(int $id): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM documents WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $document = $stmt->fetch();
        if (!$document) {
            $this->json(['error' => 'Document non trouvé'], 404);
        }

        $title = trim(strip_tags($_POST['title'] ?? ''));
        $description = trim(strip_tags($_POST['description'] ?? ''));
        $category = $_POST['category'] ?? 'other';
        $patientId = !empty($_POST['patient_id']) ? (int) $_POST['patient_id'] : null;

        $validCategories = ['prescription', 'lab_result', 'medical_report', 'administrative', 'imaging', 'consent', 'other'];
        if (!in_array($category, $validCategories, true)) $category = 'other';

        if ($title === '') {
            $this->json(['error' => 'Validation échouée', 'errors' => ['title' => 'Le titre est obligatoire.']], 422);
        }
        if ($patientId && !Patient::find($patientId)) {
            $this->json(['error' => 'Validation échouée', 'errors' => ['patient_id' => 'Patient invalide.']], 422);
        }

        $updateStmt = $db->prepare("
            UPDATE documents SET patient_id = :patient_id, title = :title, description = :description, category = :category
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':patient_id' => $patientId,
            ':title' => $title,
            ':description' => $description ?: null,
            ':category' => $category,
            ':id' => $id,
        ]);

        audit_log('update', 'documents', 'document', $id, 'Document modifié : ' . $title);

        $this->respondSuccess([], '/documents');
    }

    public function delete(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM documents WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $document = $stmt->fetch();
        if (!$document) {
            $this->json(['error' => 'Document non trouvé'], 404);
        }

        $db->prepare("DELETE FROM documents WHERE id = :id")->execute([':id' => $id]);

        $filePath = BASE_PATH . '/public' . $document['file_path'];
        if (is_file($filePath)) {
            @unlink($filePath);
        }

        audit_log('delete', 'documents', 'document', $id, 'Document supprimé : ' . $document['title']);

        $this->json(['success' => true]);
    }
}
