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

$required_fields = ['nom', 'prenom', 'email', 'mot_de_passe', 'telephone', 'numero_permis', 'date_expiration_permis'];
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

    // Check if numero_permis already exists
    $stmt = $db->prepare("SELECT id FROM chauffeurs WHERE numero_permis = ?");
    $stmt->execute([$data['numero_permis']]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'License number already exists']);
        exit;
    }

    // Insert user
    $hashed_password = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
    $stmt = $db->prepare("
        INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut)
        VALUES (?, ?, ?, ?, ?, 'chauffeur', 'Actif')
    ");
    $stmt->execute([
        trim($data['nom']),
        trim($data['prenom']),
        trim($data['email']),
        $hashed_password,
        trim($data['telephone'])
    ]);
    $user_id = $db->lastInsertId();

    // Insert chauffeur
    $stmt = $db->prepare("
        INSERT INTO chauffeurs (utilisateur_id, numero_permis, date_expiration_permis, nombre_accidents, statut)
        VALUES (?, ?, ?, 0, 'Actif')
    ");
    $stmt->execute([
        $user_id,
        trim($data['numero_permis']),
        $data['date_expiration_permis']
    ]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Chauffeur created successfully',
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
