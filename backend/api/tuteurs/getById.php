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

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Tuteur ID is required']);
    exit;
}

$id = $_GET['id'];

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("
        SELECT u.id, u.nom, u.prenom, u.email, u.telephone, u.statut, u.date_creation
        FROM utilisateurs u
        WHERE u.id = ? AND u.role = 'tuteur'
    ");
    $stmt->execute([$id]);
    $tuteur = $stmt->fetch();

    if (!$tuteur) {
        http_response_code(404);
        echo json_encode(['error' => 'Tuteur not found']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'data' => $tuteur
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
