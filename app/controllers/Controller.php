<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../helpers/Database.php';
require_once __DIR__ . '/../helpers/Session.php';

class Controller {
    protected $db;
    protected $session;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->session = new Session();
        $this->session->start();
    }

    protected function view($view, $data = []) {
        extract($data);
        
        $viewPath = APP_ROOT . '/app/views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            throw new Exception("View file not found: $view");
        }
        
        ob_start();
        require_once $viewPath;
        $content = ob_get_clean();
        
        require_once APP_ROOT . '/app/views/layouts/main.php';
    }

    protected function redirect($url) {
        header('Location: ' . APP_URL . $url);
        exit();
    }

    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    protected function validateRequest($rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            if (strpos($rule, 'required') !== false && empty($_REQUEST[$field])) {
                $errors[$field] = ucfirst($field) . ' is required';
            }
        }
        
        return $errors;
    }
} 