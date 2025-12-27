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

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("
        SELECT i.id, i.date_inscription, i.statut, i.montant_inscription, i.date_creation,
               e.nom as eleve_nom, e.prenom as eleve_prenom, e.date_naissance,
               t.nom as tuteur_nom, t.prenom as tuteur_prenom,
               tr.nom as trajet_nom, tr.tarif_mensuel,
               b.numero as bus_numero
        FROM inscriptions i
        JOIN eleves e ON i.eleve_id = e.id
        JOIN tuteurs tu ON i.tuteur_id = tu.id
        JOIN utilisateurs t ON tu.utilisateur_id = t.id
        JOIN trajets tr ON i.trajet_id = tr.id
        LEFT JOIN bus b ON tr.bus_id = b.id
        ORDER BY i.date_creation DESC
    ");
    $stmt->execute();
    $inscriptions = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $inscriptions
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
