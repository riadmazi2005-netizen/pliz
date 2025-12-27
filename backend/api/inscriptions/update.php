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
    echo json_encode(['error' => 'Inscription ID is required']);
    exit;
}

$id = $data['id'];

try {
    $db = Database::getInstance()->getConnection();

    // Check if inscription exists
    $stmt = $db->prepare("SELECT id FROM inscriptions WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Inscription not found']);
        exit;
    }

    // Update inscription information
    $update_fields = [];
    $update_values = [];

    if (isset($data['statut'])) {
        $update_fields[] = "statut = ?";
        $update_values[] = $data['statut'];
    }
    if (isset($data['montant_inscription'])) {
        $update_fields[] = "montant_inscription = ?";
        $update_values[] = $data['montant_inscription'];
    }

    if (!empty($update_fields)) {
        $update_values[] = $id;
        $stmt = $db->prepare("UPDATE inscriptions SET " . implode(', ', $update_fields) . " WHERE id = ?");
        $stmt->execute($update_values);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Inscription updated successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
