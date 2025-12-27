<?php
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';

use Firebase\JWT\JWT;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !isset($data['password']) || !isset($data['role'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Email, password, and role are required']);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];
$role = $data['role'];

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("SELECT id, nom, prenom, email, mot_de_passe, role, statut FROM utilisateurs WHERE email = ? AND role = ?");
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['mot_de_passe'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }

    if ($user['statut'] !== 'Actif') {
        http_response_code(403);
        echo json_encode(['error' => 'Account is not active']);
        exit;
    }

    // Generate JWT token
    $secret_key = "your_secret_key_here"; // Change this to a secure key
    $issued_at = time();
    $expiration_time = $issued_at + (60 * 60 * 24); // 24 hours
    $payload = array(
        "iss" => "transport-scolaire-backend",
        "aud" => "transport-scolaire-frontend",
        "iat" => $issued_at,
        "exp" => $expiration_time,
        "data" => array(
            "id" => $user['id'],
            "nom" => $user['nom'],
            "prenom" => $user['prenom'],
            "email" => $user['email'],
            "role" => $user['role']
        )
    );

    $jwt = JWT::encode($payload, $secret_key, 'HS256');

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'email' => $user['email'],
            'role' => $user['role']
        ],
        'token' => $jwt
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
