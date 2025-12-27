<?php
require_once '../../config/database.php';

// Headers pour la sécurité et CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$required_fields = ['nom', 'prenom', 'email', 'mot_de_passe', 'telephone'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty(trim($data[$field]))) {
        http_response_code(400);
        echo json_encode(['error' => "Le champ '$field' est obligatoire"]);
        exit;
    }
}

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Vérifier si l'email existe déjà
    $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->execute([trim($data['email'])]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Cet email est déjà utilisé']);
        exit;
    }

    // Hachage du mot de passe
    $hashed_password = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);

    // Insertion de l'utilisateur (Rôle 'tuteur' par défaut)
    $stmt = $db->prepare("
        INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut)
        VALUES (?, ?, ?, ?, ?, 'tuteur', 'Actif')
    ");
    
    $stmt->execute([
        trim($data['nom']),
        trim($data['prenom']),
        trim($data['email']),
        $hashed_password,
        trim($data['telephone'])
    ]);
    
    $user_id = $db->lastInsertId();
    $db->commit();

    // Initialiser la session pour connecter l'utilisateur immédiatement
    session_start();
    $_SESSION['user_id'] = $user_id;
    $_SESSION['role'] = 'tuteur';

    echo json_encode([
        'success' => true,
        'message' => 'Inscription réussie',
        'user' => [
            'id' => $user_id,
            'nom' => trim($data['nom']),
            'prenom' => trim($data['prenom']),
            'email' => trim($data['email']),
            'role' => 'tuteur'
        ]
    ]);

} catch (Exception $e) {
    if (isset($db)) { $db->rollBack(); }
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>