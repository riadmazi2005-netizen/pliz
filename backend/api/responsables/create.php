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

$required_fields = ['nom', 'prenom', 'email', 'mot_de_passe', 'telephone', 'zone_responsabilite'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty(trim($data[$field]))) {
        http_response_code(400);
        echo json_encode(['error' => "Field '$field' is required"]);
        exit;
    }
}

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

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
        VALUES (?, ?, ?, ?, ?, 'responsable', 'Actif')
    ");
    $stmt->execute([
        trim($data['nom']),
        trim($data['prenom']),
        trim($data['email']),
        $hashed_password,
        trim($data['telephone'])
    ]);
    $user_id = $db->lastInsertId();

    // Insert responsable
    $stmt = $db->prepare("
        INSERT INTO responsables_bus (utilisateur_id, zone_responsabilite, statut)
        VALUES (?, ?, 'Actif')
    ");
    $stmt->execute([
        $user_id,
        trim($data['zone_responsabilite'])
    ]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Responsable created successfully',
        'data' => [
            'id' => $db->lastInsertId(),
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email']
        ]
    ]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
