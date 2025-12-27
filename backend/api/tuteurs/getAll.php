<?php
// backend/api/tuteurs/getAll.php
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

try {
    $db = Database::getInstance()->getConnection();

    // Récupération de tous les tuteurs avec statistiques
    $stmt = $db->prepare("
        SELECT 
            u.id, 
            u.nom, 
            u.prenom, 
            u.email, 
            u.telephone, 
            u.statut, 
            u.date_creation,
            COUNT(DISTINCT e.id) as nombre_eleves,
            COUNT(DISTINCT CASE 
                WHEN p.statut = 'Impayé' THEN p.id 
                ELSE NULL 
            END) as paiements_impayes
        FROM utilisateurs u
        LEFT JOIN eleves e ON u.id = e.tuteur_id
        LEFT JOIN inscriptions i ON e.id = i.eleve_id
        LEFT JOIN paiements p ON i.id = p.inscription_id
        WHERE u.role = 'tuteur'
        GROUP BY u.id, u.nom, u.prenom, u.email, u.telephone, u.statut, u.date_creation
        ORDER BY u.nom, u.prenom
    ");
    
    $stmt->execute();
    $tuteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count' => count($tuteurs),
        'data' => $tuteurs
    ]);

} catch (PDOException $e) {
    error_log("Erreur récupération tuteurs DB: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la récupération des tuteurs']);
} catch (Exception $e) {
    error_log("Erreur récupération tuteurs: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}
?>