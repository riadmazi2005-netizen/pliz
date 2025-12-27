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
    echo json_encode(['error' => 'Accident ID is required']);
    exit;
}

$id = $data['id'];

try {
    $db = Database::getInstance()->getConnection();

    // Check if accident exists
    $stmt = $db->prepare("SELECT id FROM accidents WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Accident not found']);
        exit;
    }

    // Update accident information
    $update_fields = [];
    $update_values = [];

    if (isset($data['description'])) {
        $update_fields[] = "description = ?";
        $update_values[] = trim($data['description']);
    }
    if (isset($data['lieu'])) {
        $update_fields[] = "lieu = ?";
        $update_values[] = trim($data['lieu']);
    }
    if (isset($data['gravite'])) {
        $update_fields[] = "gravite = ?";
        $update_values[] = $data['gravite'];
    }
    if (isset($data['nombre_blesses'])) {
        $update_fields[] = "nombre_blesses = ?";
        $update_values[] = $data['nombre_blesses'];
    }
    if (isset($data['nombre_deces'])) {
        $update_fields[] = "nombre_deces = ?";
        $update_values[] = $data['nombre_deces'];
    }
    if (isset($data['cout_estime'])) {
        $update_fields[] = "cout_estime = ?";
        $update_values[] = $data['cout_estime'];
    }
    if (isset($data['statut'])) {
        $update_fields[] = "statut = ?";
        $update_values[] = $data['statut'];
    }

    if (!empty($update_fields)) {
        $update_values[] = $id;
        $stmt = $db->prepare("UPDATE accidents SET " . implode(', ', $update_fields) . " WHERE id = ?");
        $stmt->execute($update_values);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Accident updated successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
