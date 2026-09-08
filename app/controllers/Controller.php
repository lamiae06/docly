<?php
namespace App\Controllers;

/**
 * Docly - Contrôleur de Base
 */
abstract class Controller {

    /**
     * Rend une vue
     */
    protected function view(string $view, array $data = [], string $layout = 'main'): void {
        extract($data);

        $viewFile = APP_PATH . '/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new \Exception("Vue non trouvée: $view");
        }

        // Start output buffering for the view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Render layout with content
        $layoutFile = APP_PATH . '/views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Rend une vue partielle
     */
    protected function partial(string $partial, array $data = []): void {
        extract($data);
        $file = APP_PATH . '/views/partials/' . str_replace('.', '/', $partial) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }

    /**
     * Réponse JSON
     */
    protected function json(array $data, int $status = 200): void {
        json_response($data, $status);
    }

    /**
     * Réponse de succès qui s'adapte au type de soumission : si la requête
     * vient du fetch() JS (en-tête X-CSRF-Token, injecté uniquement par
     * docly.js), on renvoie du JSON comme d'habitude pour que le SPA gère
     * la redirection lui-même. Si elle vient d'une soumission de formulaire
     * "nature" (repli sans JavaScript), on fait une vraie redirection HTTP
     * pour ne jamais laisser l'utilisateur face à une page JSON brute.
     */
    protected function respondSuccess(array $data, string $redirectUrl): void {
        $isAjax = !empty($_SERVER['HTTP_X_CSRF_TOKEN']);
        if ($isAjax) {
            $this->json(array_merge(['success' => true, 'redirect' => $redirectUrl], $data));
        }
        $this->redirect($redirectUrl);
    }

    /**
     * Redirection
     */
    protected function redirect(string $url): void {
        redirect($url);
    }

    /**
     * Vérifie la méthode HTTP
     */
    protected function isMethod(string $method): bool {
        return $_SERVER['REQUEST_METHOD'] === strtoupper($method);
    }

    /**
     * Récupère les données POST validées
     */
    protected function input(array $fields): array {
        $data = [];
        foreach ($fields as $field => $rules) {
            $value = $_POST[$field] ?? null;
            $data[$field] = $this->sanitize($value, $rules);
        }
        return $data;
    }

    /**
     * Nettoie une valeur
     */
    protected function sanitize($value, string $type) {
        if ($value === null || $value === '') return null;

        switch ($type) {
            case 'string':
                return trim(strip_tags($value));
            case 'email':
                return filter_var(trim($value), FILTER_SANITIZE_EMAIL);
            case 'int':
                $int = filter_var($value, FILTER_VALIDATE_INT);
                return $int === false ? null : $int;
            case 'float':
                $float = filter_var($value, FILTER_VALIDATE_FLOAT);
                return $float === false ? null : $float;
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'date':
                $ts = strtotime($value);
                return $ts === false ? null : date('Y-m-d', $ts);
            default:
                return trim($value);
        }
    }

    /**
     * Valide les données
     */
    protected function validate(array $data, array $rules): array {
        $errors = [];
        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            $rulesArray = explode('|', $ruleSet);

            foreach ($rulesArray as $rule) {
                if ($rule === 'required' && empty($value)) {
                    $errors[$field] = "Le champ est obligatoire.";
                    break;
                }
                if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "L'adresse email n'est pas valide.";
                    break;
                }
                if (str_starts_with($rule, 'min:')) {
                    $min = (int) substr($rule, 4);
                    if (strlen((string)$value) < $min) {
                        $errors[$field] = "Minimum $min caractères requis.";
                        break;
                    }
                }
                if (str_starts_with($rule, 'max:')) {
                    $max = (int) substr($rule, 4);
                    if (strlen((string)$value) > $max) {
                        $errors[$field] = "Maximum $max caractères autorisés.";
                        break;
                    }
                }
            }
        }
        return $errors;
    }
}
