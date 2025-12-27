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

$required_fields = ['numero', 'marque', 'modele', 'capacite'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty(trim($data[$field]))) {
        http_response_code(400);
        echo json_encode(['error' => "Field '$field' is required"]);
        exit;
    }
}

try {
    $db = Database::getInstance()->getConnection();

    // Check if bus numero already exists
    $stmt = $db->prepare("SELECT id FROM bus WHERE numero = ?");
    $stmt->execute([$data['numero']]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Bus number already exists']);
        exit;
    }

    $stmt = $db->prepare("
        INSERT INTO bus (numero, marque, modele, annee_fabrication, capacite, chauffeur_id, responsable_id, statut)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Actif')
    ");
    $stmt->execute([
        trim($data['numero']),
        trim($data['marque']),
        trim($data['modele']),
        $data['annee_fabrication'] ?? null,
        $data['capacite'],
        $data['chauffeur_id'] ?? null,
        $data['responsable_id'] ?? null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Bus created successfully',
        'data' => [
            'id' => $db->lastInsertId(),
            'numero' => $data['numero'],
            'marque' => $data['marque'],
            'modele' => $data['modele']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
