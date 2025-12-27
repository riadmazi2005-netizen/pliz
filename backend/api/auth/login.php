<?php
// backend/api/auth/login.php
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

// Récupération et validation des données JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Format JSON invalide']);
    exit;
}

// Validation des champs requis
$required_fields = ['email', 'password', 'role'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty(trim($data[$field]))) {
        http_response_code(400);
        echo json_encode(['error' => "Le champ '$field' est obligatoire"]);
        exit;
    }
}

$email = trim($data['email']);
$password = $data['password'];
$role = $data['role'];

// Validation du rôle
$valid_roles = ['admin', 'chauffeur', 'responsable', 'tuteur'];
if (!in_array($role, $valid_roles)) {
    http_response_code(400);
    echo json_encode(['error' => 'Rôle invalide']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Récupération de l'utilisateur
    $stmt = $db->prepare("
        SELECT id, nom, prenom, email, mot_de_passe, role, statut, telephone 
        FROM utilisateurs 
        WHERE email = ? AND role = ?
    ");
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérification des identifiants
    if (!$user || !password_verify($password, $user['mot_de_passe'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Email ou mot de passe incorrect'
        ]);
        exit;
    }

    // Vérification du statut du compte
    if ($user['statut'] !== 'Actif') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Votre compte est inactif. Contactez l\'administrateur.'
        ]);
        exit;
    }

    // Démarrage de la session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Régénérer l'ID de session pour la sécurité
    session_regenerate_id(true);
    
    // Stockage des informations en session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['login_time'] = time();

    // Préparation de la réponse (sans le mot de passe)
    unset($user['mot_de_passe']);

    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie',
        'user' => $user
    ]);

} catch (PDOException $e) {
    error_log("Erreur de connexion DB: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur lors de la connexion']);
} catch (Exception $e) {
    error_log("Erreur login: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}
?>