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

$required_fields = ['bus_id', 'chauffeur_id', 'date_accident', 'description', 'lieu', 'gravite'];
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

    // Check if bus exists
    $stmt = $db->prepare("SELECT id FROM bus WHERE id = ?");
    $stmt->execute([$data['bus_id']]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Bus not found']);
        exit;
    }

    // Check if chauffeur exists
    $stmt = $db->prepare("SELECT id FROM chauffeurs WHERE id = ?");
    $stmt->execute([$data['chauffeur_id']]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Chauffeur not found']);
        exit;
    }

    // Insert accident
    $stmt = $db->prepare("
        INSERT INTO accidents (bus_id, chauffeur_id, date_accident, description, lieu, gravite,
                              nombre_blesses, nombre_deces, cout_estime, statut)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'En cours')
    ");
    $stmt->execute([
        $data['bus_id'],
        $data['chauffeur_id'],
        $data['date_accident'],
        trim($data['description']),
        trim($data['lieu']),
        $data['gravite'],
        $data['nombre_blesses'] ?? 0,
        $data['nombre_deces'] ?? 0,
        $data['cout_estime'] ?? 0.00
    ]);

    // Update chauffeur accident count
    $stmt = $db->prepare("UPDATE chauffeurs SET nombre_accidents = nombre_accidents + 1 WHERE id = ?");
    $stmt->execute([$data['chauffeur_id']]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Accident reported successfully',
        'data' => [
            'id' => $db->lastInsertId(),
            'bus_id' => $data['bus_id'],
            'chauffeur_id' => $data['chauffeur_id']
        ]
    ]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
