<?php
require_once '../../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$required_fields = ['nom', 'prenom', 'email', 'mot_de_passe', 'telephone'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty(trim($data[$field]))) {
        http_response_code(400);
        echo json_encode(['error' => "Field '$field' is required"]);
        exit;
    }
}

try {
    $db = Database::getInstance()->getConnection();

    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Email already exists']);
        exit;
    }

    // Insert user
    $hashed_password = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
    $stmt = $db->prepare("
        INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut)
        VALUES (?, ?, ?, ?, ?, 'tuteur', 'Actif')
    ");
    $stmt->execute([
        trim($data['nom']),
        trim($data['prenom']),
        trim($data['email']),
        $hashed_password,
        trim($data['telephone'])
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Tuteur created successfully',
        'data' => [
            'id' => $db->lastInsertId(),
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'role' => 'tuteur'
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
