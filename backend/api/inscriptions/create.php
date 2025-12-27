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

$required_fields = ['eleve_id', 'bus_id', 'date_debut', 'date_fin', 'montant_mensuel'];
foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Field '$field' is required"]);
        exit;
    }
}

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Check if eleve exists
    $stmt = $db->prepare("SELECT id FROM eleves WHERE id = ?");
    $stmt->execute([$data['eleve_id']]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Eleve not found']);
        exit;
    }

    // Check if bus exists and is active
    $stmt = $db->prepare("SELECT id, statut, capacite FROM bus WHERE id = ?");
    $stmt->execute([$data['bus_id']]);
    $bus = $stmt->fetch();

    if (!$bus) {
        http_response_code(404);
        echo json_encode(['error' => 'Bus not found']);
        exit;
    }

    if ($bus['statut'] !== 'Actif') {
        http_response_code(400);
        echo json_encode(['error' => 'Bus is not active']);
        exit;
    }

    // Check bus capacity
    $stmt = $db->prepare("SELECT COUNT(*) as current_students FROM inscriptions WHERE bus_id = ? AND statut = 'Active'");
    $stmt->execute([$data['bus_id']]);
    $current_count = $stmt->fetch()['current_students'];

    if ($current_count >= $bus['capacite']) {
        http_response_code(409);
        echo json_encode(['error' => 'Bus is at full capacity']);
        exit;
    }

    // Check if eleve is already enrolled in another active inscription
    $stmt = $db->prepare("SELECT id FROM inscriptions WHERE eleve_id = ? AND statut = 'Active'");
    $stmt->execute([$data['eleve_id']]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Eleve is already enrolled in an active inscription']);
        exit;
    }

    // Create inscription
    $stmt = $db->prepare("
        INSERT INTO inscriptions (eleve_id, bus_id, date_inscription, date_debut, date_fin, statut, montant_mensuel)
        VALUES (?, ?, NOW(), ?, ?, 'Active', ?)
    ");
    $stmt->execute([
        $data['eleve_id'],
        $data['bus_id'],
        $data['date_debut'],
        $data['date_fin'],
        $data['montant_mensuel']
    ]);

    $inscription_id = $db->lastInsertId();

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Inscription created successfully',
        'data' => [
            'id' => $inscription_id,
            'eleve_id' => $data['eleve_id'],
            'bus_id' => $data['bus_id']
        ]
    ]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
