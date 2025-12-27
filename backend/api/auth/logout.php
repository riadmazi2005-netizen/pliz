<?php
// backend/api/auth/logout.php
require_once '../../config/database.php';

// Configuration CORS sécurisée
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

try {
    // Démarrer la session si elle existe
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Détruire toutes les variables de session
    $_SESSION = array();

    // Supprimer le cookie de session si il existe
    if (isset($_COOKIE[session_name()])) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    // Détruire la session
    session_destroy();

    echo json_encode([
        'success' => true,
        'message' => 'Déconnexion réussie'
    ]);

} catch (Exception $e) {
    error_log("Erreur logout: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la déconnexion']);
}
?>