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

if (!isset($_GET['date'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Date is required']);
    exit;
}

$date = $_GET['date'];

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("
        SELECT p.id, p.date_presence, p.statut, p.heure_montage, p.heure_descente, p.commentaire,
               e.nom as eleve_nom, e.prenom as eleve_prenom, e.classe,
               t.nom_trajet, t.heure_depart, t.heure_arrivee,
               b.numero as bus_numero,
               c.nom as chauffeur_nom, c.prenom as chauffeur_prenom
        FROM presences p
        JOIN eleves e ON p.eleve_id = e.id
        LEFT JOIN trajets t ON p.trajet_id = t.id
        LEFT JOIN bus b ON t.bus_id = b.id
        LEFT JOIN chauffeurs ch ON b.chauffeur_id = ch.id
        LEFT JOIN utilisateurs c ON ch.utilisateur_id = c.id
        WHERE DATE(p.date_presence) = ?
        ORDER BY p.heure_montage, e.nom, e.prenom
    ");
    $stmt->execute([$date]);
    $presences = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $presences
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
