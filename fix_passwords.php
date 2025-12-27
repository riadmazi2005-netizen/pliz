<?php
require_once 'backend/config/database.php';

echo "Updating passwords...\n";

try {
    $db = Database::getInstance()->getConnection();

    // Define test users and their new passwords
    $users = [
        ['email' => 'admin@transport-scolaire.ma', 'password' => 'admin123', 'role' => 'admin'],
        ['email' => 'jean.dupont@transport-scolaire.ma', 'password' => 'chauffeur123', 'role' => 'chauffeur'],
        ['email' => 'marie.martin@transport-scolaire.ma', 'password' => 'responsable123', 'role' => 'responsable'],
        ['email' => 'pierre.dubois@email.com', 'password' => 'tuteur123', 'role' => 'tuteur']
    ];

    foreach ($users as $user) {
        $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);

        $stmt = $db->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ? AND role = ?");
        $stmt->execute([$hashedPassword, $user['email'], $user['role']]);

        if ($stmt->rowCount() > 0) {
            echo "✓ Updated password for {$user['email']}\n";
        } else {
            echo "✗ No user found for {$user['email']}\n";
        }
    }

    echo "\nAll passwords updated successfully!\n\n";

    // Verify updates
    echo "Verifying updates...\n";
    foreach ($users as $user) {
        $stmt = $db->prepare("SELECT mot_de_passe FROM utilisateurs WHERE email = ? AND role = ?");
        $stmt->execute([$user['email'], $user['role']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && password_verify($user['password'], $result['mot_de_passe'])) {
            echo "User: {$user['email']} ({$user['role']}) - Password hash exists: Yes\n";
        } else {
            echo "User: {$user['email']} ({$user['role']}) - Password hash exists: No\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
