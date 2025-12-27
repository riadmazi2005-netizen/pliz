<?php
require_once '../../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Bus ID is required']);
    exit;
}

$id = $data['id'];

try {
    $db = Database::getInstance()->getConnection();

    // Check if bus exists
    $stmt = $db->prepare("SELECT id FROM bus WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Bus not found']);
        exit;
    }

    // Check if bus has active inscriptions
    $stmt = $db->prepare("SELECT id FROM inscriptions WHERE bus_id = ? AND statut = 'Active'");
    $stmt->execute([$id]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Cannot delete bus with active inscriptions']);
        exit;
    }

    // Delete bus
    $stmt = $db->prepare("DELETE FROM bus WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => 'Bus deleted successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
