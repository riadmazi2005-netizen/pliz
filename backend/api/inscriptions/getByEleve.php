<?php
// backend/api/inscriptions/getByEleve.php
require_once '../../config/database.php';

// Configuration CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gestion des requêtes OPTIONS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Vérifier que l'ID de l'élève est fourni
if (!isset($_GET['eleve_id']) || empty($_GET['eleve_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'L\'ID de l\'élève est requis']);
    exit;
}

$eleve_id = $_GET['eleve_id'];

try {
    $db = Database::getInstance()->getConnection();

    // Récupération des inscriptions de l'élève
    $stmt = $db->prepare("
        SELECT 
            i.id,
            i.eleve_id,
            i.bus_id,
            i.date_inscription,
            i.date_debut,
            i.date_fin,
            i.statut,
            i.montant_mensuel,
            i.date_creation,
            b.numero as bus_numero,
            b.marque as bus_marque,
            b.modele as bus_modele,
            b.immatriculation as bus_immatriculation,
            b.capacite as bus_capacite,
            b.statut as bus_statut
        FROM inscriptions i
        LEFT JOIN bus b ON i.bus_id = b.id
        WHERE i.eleve_id = ?
        ORDER BY i.date_creation DESC
    ");
    
    $stmt->execute([$eleve_id]);
    $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count' => count($inscriptions),
        'data' => $inscriptions
    ]);

} catch (PDOException $e) {
    error_log("Erreur récupération inscriptions élève DB: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la récupération des inscriptions']);
} catch (Exception $e) {
    error_log("Erreur récupération inscriptions élève: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}
?>