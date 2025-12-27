<?php
require_once '../../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit(0);

// Récupération des données JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['email']) || !isset($input['password']) || !isset($input['role'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Données manquantes']);
    exit;
}

$email = trim($input['email']);
$password = $input['password'];
$role = $input['role'];

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, nom, prenom, email, mot_de_passe, role, statut FROM utilisateurs WHERE email = ? AND role = ?");
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['mot_de_passe'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Identifiants invalides']);
        exit;
    }

    if ($user['statut'] !== 'Actif') {
        http_response_code(401);
        echo json_encode(['error' => 'Compte inactif']);
        exit;
    }

    // AUTHENTICATION PAR SESSION
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['user_email'] = $user['email'];

    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie',
        'user' => [
            'id' => $user['id'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}
