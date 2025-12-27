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

$required_fields = ['destinataire_id', 'destinataire_type', 'titre', 'message'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty(trim($data[$field]))) {
        http_response_code(400);
        echo json_encode(['error' => "Field '$field' is required"]);
        exit;
    }
}

try {
    $db = Database::getInstance()->getConnection();

    // Validate destinataire exists
    $table = '';
    if ($data['destinataire_type'] === 'tuteur') {
        $table = 'utilisateurs';
    } elseif ($data['destinataire_type'] === 'chauffeur') {
        $table = 'chauffeurs';
    } elseif ($data['destinataire_type'] === 'responsable') {
        $table = 'responsables_bus';
    } elseif ($data['destinataire_type'] === 'admin') {
        $table = 'utilisateurs';
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid destinataire type']);
        exit;
    }

    $stmt = $db->prepare("SELECT id FROM $table WHERE id = ?");
    $stmt->execute([$data['destinataire_id']]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Destinataire not found']);
        exit;
    }

    // Create notification
    $stmt = $db->prepare("
        INSERT INTO notifications (destinataire_id, destinataire_type, titre, message, statut, date_creation)
        VALUES (?, ?, ?, ?, 'Non lue', NOW())
    ");
    $stmt->execute([
        $data['destinataire_id'],
        $data['destinataire_type'],
        trim($data['titre']),
        trim($data['message'])
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Notification created successfully',
        'data' => [
            'id' => $db->lastInsertId(),
            'destinataire_id' => $data['destinataire_id'],
            'destinataire_type' => $data['destinataire_type']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
