<?php
require_once 'backend/config/database.php';

try {
    $db = Database::getInstance()->getConnection();

    // Hash the password "admin123"
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);

    // Update admin password
    $stmt = $db->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ? AND role = 'admin'");
    $stmt->execute([$hashedPassword, 'admin@transport-scolaire.ma']);

    // Hash chauffeur password
    $chauffeurPassword = password_hash('chauffeur123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ? AND role = 'chauffeur'");
    $stmt->execute([$chauffeurPassword, 'jean.dupont@transport-scolaire.ma']);

    // Hash responsable password
    $responsablePassword = password_hash('responsable123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ? AND role = 'responsable'");
    $stmt->execute([$responsablePassword, 'marie.martin@transport-scolaire.ma']);

    // Hash tuteur password
    $tuteurPassword = password_hash('tuteur123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ? AND role = 'tuteur'");
    $stmt->execute([$tuteurPassword, 'pierre.dubois@email.com']);

    echo "Passwords updated successfully!\n";
    echo "Admin: admin@transport-scolaire.ma / admin123\n";
    echo "Chauffeur: jean.dupont@transport-scolaire.ma / chauffeur123\n";
    echo "Responsable: marie.martin@transport-scolaire.ma / responsable123\n";
    echo "Tuteur: pierre.dubois@email.com / tuteur123\n";

} catch (Exception $e) {
    echo "Error updating passwords: " . $e->getMessage();
}
?>
