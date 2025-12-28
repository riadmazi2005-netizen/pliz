<?php
require_once '../../config/headers.php';
require_once '../../config/database.php';
require_once '../../config/jwt.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !isset($data['password']) || !isset($data['role'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email, mot de passe et rôle requis']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE email = ? AND role = ?');
    $stmt->execute([$data['email'], $data['role']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
        exit;
    }
    
    if (!password_verify($data['password'], $user['mot_de_passe'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
        exit;
    }
    
    if ($user['statut'] !== 'Actif') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Compte désactivé']);
        exit;
    }
    
    // Générer le token JWT
    $tokenPayload = [
        'id' => $user['id'],
        'email' => $user['email'],
        'role' => $user['role'],
        'exp' => time() + (7 * 24 * 60 * 60) // 7 jours
    ];
    $token = generateToken($tokenPayload);
    
    // Retirer le mot de passe de la réponse
    unset($user['mot_de_passe']);
    
    echo json_encode([
        'success' => true,
        'token' => $token,
        'user' => $user
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la connexion']);
}
?>




