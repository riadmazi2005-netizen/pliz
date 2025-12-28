<?php
/**
 * Script pour créer et mettre à jour les comptes de test avec les vrais hash bcrypt
 * Exécutez ce script une fois: http://localhost/backend/create_and_update_test_accounts.php
 * 
 * Ce script:
 * 1. Crée les comptes s'ils n'existent pas
 * 2. Met à jour les mots de passe avec les vrais hash bcrypt
 */

require_once 'config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDBConnection();
    
    // Générer les hash bcrypt pour les mots de passe
    $password_admin = password_hash('admin123', PASSWORD_DEFAULT);
    $password_respo = password_hash('respo123', PASSWORD_DEFAULT);
    $password_chauffeur = password_hash('chauffeur123', PASSWORD_DEFAULT);
    
    $results = [];
    
    // Fonction pour créer ou mettre à jour un compte
    function createOrUpdateAccount($pdo, $nom, $prenom, $email, $password, $telephone, $role) {
        // Vérifier si le compte existe
        $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = ? AND role = ?');
        $stmt->execute([$email, $role]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Mettre à jour le mot de passe
            $stmt = $pdo->prepare('UPDATE utilisateurs SET mot_de_passe = ?, nom = ?, prenom = ?, telephone = ?, statut = ? WHERE id = ?');
            $stmt->execute([$password, $nom, $prenom, $telephone, 'Actif', $existing['id']]);
            return ['action' => 'updated', 'id' => $existing['id']];
        } else {
            // Créer le compte
            $stmt = $pdo->prepare('INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$nom, $prenom, $email, $password, $telephone, $role, 'Actif']);
            return ['action' => 'created', 'id' => $pdo->lastInsertId()];
        }
    }
    
    // Créer ou mettre à jour l'admin
    $adminResult = createOrUpdateAccount($pdo, 'Admin', 'Système', 'admin@mohamme5.ma', $password_admin, '0612345678', 'admin');
    $results['admin'] = $adminResult;
    
    // Créer ou mettre à jour le responsable
    $respoResult = createOrUpdateAccount($pdo, 'Responsable', 'Bus', 'responsable_bus@mohammed5.ma', $password_respo, '0612345680', 'responsable');
    $results['responsable'] = $respoResult;
    
    // Créer ou mettre à jour le chauffeur
    $chauffeurResult = createOrUpdateAccount($pdo, 'Chauffeur', 'Test', 'chauffeur@mohammed5.ma', $password_chauffeur, '0612345681', 'chauffeur');
    $results['chauffeur'] = $chauffeurResult;
    
    // Récupérer les comptes créés/mis à jour
    $stmt = $pdo->prepare('SELECT id, nom, prenom, email, role, statut FROM utilisateurs WHERE email IN (?, ?, ?)');
    $stmt->execute(['admin@mohamme5.ma', 'responsable_bus@mohammed5.ma', 'chauffeur@mohammed5.ma']);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Comptes créés/mis à jour avec succès',
        'results' => $results,
        'accounts' => $accounts,
        'credentials' => [
            'admin' => [
                'email' => 'admin@mohamme5.ma',
                'password' => 'admin123'
            ],
            'responsable' => [
                'email' => 'responsable_bus@mohammed5.ma',
                'password' => 'respo123'
            ],
            'chauffeur' => [
                'email' => 'chauffeur@mohammed5.ma',
                'password' => 'chauffeur123'
            ]
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>

