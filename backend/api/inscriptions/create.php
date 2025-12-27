<?php
// backend/api/inscriptions/create.php
require_once '../../config/database.php';

// Configuration CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gestion des requêtes OPTIONS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Récupération et validation des données
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Format JSON invalide']);
    exit;
}

// Validation des champs requis selon le schéma de la base de données
$required_fields = ['eleve_id', 'bus_id', 'montant_mensuel'];
foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Le champ '$field' est obligatoire"]);
        exit;
    }
}

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Vérifier que l'élève existe
    $stmt = $db->prepare("SELECT id, tuteur_id, statut FROM eleves WHERE id = ?");
    $stmt->execute([$data['eleve_id']]);
    $eleve = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$eleve) {
        $db->rollBack();
        http_response_code(404);
        echo json_encode(['error' => 'Élève non trouvé']);
        exit;
    }

    // Vérifier que le bus existe et est actif
    $stmt = $db->prepare("SELECT id, statut, capacite FROM bus WHERE id = ?");
    $stmt->execute([$data['bus_id']]);
    $bus = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bus) {
        $db->rollBack();
        http_response_code(404);
        echo json_encode(['error' => 'Bus non trouvé']);
        exit;
    }

    if ($bus['statut'] !== 'Actif') {
        $db->rollBack();
        http_response_code(400);
        echo json_encode(['error' => 'Le bus n\'est pas actif']);
        exit;
    }

    // Vérifier la capacité du bus
    $stmt = $db->prepare("
        SELECT COUNT(*) as nb_inscrits 
        FROM inscriptions 
        WHERE bus_id = ? AND statut = 'Active'
    ");
    $stmt->execute([$data['bus_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $nb_inscrits = $result['nb_inscrits'];

    if ($nb_inscrits >= $bus['capacite']) {
        $db->rollBack();
        http_response_code(409);
        echo json_encode(['error' => 'Le bus a atteint sa capacité maximale']);
        exit;
    }

    // Vérifier qu'il n'existe pas déjà une inscription active pour cet élève
    $stmt = $db->prepare("
        SELECT id FROM inscriptions 
        WHERE eleve_id = ? AND statut = 'Active'
    ");
    $stmt->execute([$data['eleve_id']]);
    
    if ($stmt->fetch()) {
        $db->rollBack();
        http_response_code(409);
        echo json_encode(['error' => 'L\'élève a déjà une inscription active']);
        exit;
    }

    // Préparer les dates
    $date_inscription = date('Y-m-d');
    $date_debut = isset($data['date_debut']) ? $data['date_debut'] : date('Y-m-d');
    $date_fin = isset($data['date_fin']) ? $data['date_fin'] : date('Y-m-d', strtotime('+1 year'));

    // Créer l'inscription
    $stmt = $db->prepare("
        INSERT INTO inscriptions (
            eleve_id, bus_id, date_inscription, date_debut, date_fin, 
            statut, montant_mensuel
        ) VALUES (?, ?, ?, ?, ?, 'Active', ?)
    ");
    
    $stmt->execute([
        $data['eleve_id'],
        $data['bus_id'],
        $date_inscription,
        $date_debut,
        $date_fin,
        $data['montant_mensuel']
    ]);

    $inscription_id = $db->lastInsertId();

    // Mettre à jour le statut de l'élève
    $stmt = $db->prepare("UPDATE eleves SET statut = 'Actif' WHERE id = ?");
    $stmt->execute([$data['eleve_id']]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Inscription créée avec succès',
        'data' => [
            'id' => $inscription_id,
            'eleve_id' => $data['eleve_id'],
            'bus_id' => $data['bus_id'],
            'date_inscription' => $date_inscription,
            'montant_mensuel' => $data['montant_mensuel']
        ]
    ]);

} catch (PDOException $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    error_log("Erreur création inscription DB: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la création de l\'inscription']);
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    error_log("Erreur création inscription: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}
?>