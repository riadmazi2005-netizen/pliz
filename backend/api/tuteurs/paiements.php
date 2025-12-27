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

if (!isset($_GET['tuteur_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Tuteur ID is required']);
    exit;
}

$tuteur_id = $_GET['tuteur_id'];

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("
        SELECT p.id, p.montant, p.mois, p.annee, p.date_paiement, p.mode_paiement, p.statut, p.date_creation,
               i.id as inscription_id, i.montant_mensuel,
               e.nom as eleve_nom, e.prenom as eleve_prenom
        FROM paiements p
        JOIN inscriptions i ON p.inscription_id = i.id
        JOIN eleves e ON i.eleve_id = e.id
        WHERE e.tuteur_id = ?
        ORDER BY p.date_paiement DESC
    ");
    $stmt->execute([$tuteur_id]);
    $paiements = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $paiements
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
