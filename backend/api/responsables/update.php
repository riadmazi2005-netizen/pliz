<?php
require_once '../../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Responsable ID is required']);
    exit;
}

$id = $data['id'];

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Check if responsable exists
    $stmt = $db->prepare("SELECT utilisateur_id FROM responsables_bus WHERE id = ?");
    $stmt->execute([$id]);
    $responsable = $stmt->fetch();

    if (!$responsable) {
        http_response_code(404);
        echo json_encode(['error' => 'Responsable not found']);
        exit;
    }

    $user_id = $responsable['utilisateur_id'];

    // Update user information
    $update_fields = [];
    $update_values = [];

    if (isset($data['nom'])) {
        $update_fields[] = "nom = ?";
        $update_values[] = trim($data['nom']);
    }
    if (isset($data['prenom'])) {
        $update_fields[] = "prenom = ?";
        $update_values[] = trim($data['prenom']);
    }
    if (isset($data['email'])) {
        // Check if email is already taken by another user
        $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
        $stmt->execute([$data['email'], $user_id]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Email already exists']);
            exit;
        }
        $update_fields[] = "email = ?";
        $update_values[] = trim($data['email']);
    }
    if (isset($data['telephone'])) {
        $update_fields[] = "telephone = ?";
        $update_values[] = trim($data['telephone']);
    }

    if (!empty($update_fields)) {
        $update_values[] = $user_id;
        $stmt = $db->prepare("UPDATE utilisateurs SET " . implode(', ', $update_fields) . " WHERE id = ?");
        $stmt->execute($update_values);
    }

    // Update responsable information
    $responsable_fields = [];
    $responsable_values = [];

    if (isset($data['zone_responsabilite'])) {
        $responsable_fields[] = "zone_responsabilite = ?";
        $responsable_values[] = trim($data['zone_responsabilite']);
    }
    if (isset($data['statut'])) {
        $responsable_fields[] = "statut = ?";
        $responsable_values[] = $data['statut'];
    }

    if (!empty($responsable_fields)) {
        $responsable_values[] = $id;
        $stmt = $db->prepare("UPDATE responsables_bus SET " . implode(', ', $responsable_fields) . " WHERE id = ?");
        $stmt->execute($responsable_values);
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Responsable updated successfully'
    ]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
