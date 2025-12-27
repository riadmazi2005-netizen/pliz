<?php
require_once '../../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_GET['destinataire_id']) || !isset($_GET['destinataire_type'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Destinataire ID and type are required']);
    exit;
}

$destinataire_id = $_GET['destinataire_id'];
$destinataire_type = $_GET['destinataire_type'];

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("
        SELECT id, titre, message, statut, date_creation
        FROM notifications
        WHERE destinataire_id = ? AND destinataire_type = ?
        ORDER BY date_creation DESC
    ");
    $stmt->execute([$destinataire_id, $destinataire_type]);
    $notifications = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $notifications
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
