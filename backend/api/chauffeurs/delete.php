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
    echo json_encode(['error' => 'Chauffeur ID is required']);
    exit;
}

$id = $data['id'];

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Check if chauffeur exists
    $stmt = $db->prepare("SELECT utilisateur_id FROM chauffeurs WHERE id = ?");
    $stmt->execute([$id]);
    $chauffeur = $stmt->fetch();

    if (!$chauffeur) {
        http_response_code(404);
        echo json_encode(['error' => 'Chauffeur not found']);
        exit;
    }

    $user_id = $chauffeur['utilisateur_id'];

    // Check if chauffeur is assigned to a bus
    $stmt = $db->prepare("SELECT id FROM bus WHERE chauffeur_id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Cannot delete chauffeur assigned to a bus']);
        exit;
    }

    // Delete chauffeur (this will cascade to delete the user due to foreign key constraint)
    $stmt = $db->prepare("DELETE FROM chauffeurs WHERE id = ?");
    $stmt->execute([$id]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Chauffeur deleted successfully'
    ]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
