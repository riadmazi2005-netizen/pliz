-- Script pour créer les comptes de test avec les vrais hash bcrypt
-- Exécutez ce script dans votre base de données MySQL/MariaDB

USE transport_scolaire;

-- Supprimer les anciens comptes s'ils existent
DELETE FROM utilisateurs WHERE email = 'admin@mohamme5.ma' AND role = 'admin';
DELETE FROM utilisateurs WHERE email = 'responsable_bus@mohammed5.ma' AND role = 'responsable';
DELETE FROM utilisateurs WHERE email = 'chauffeur@mohammed5.ma' AND role = 'chauffeur';

-- IMPORTANT: Pour générer les vrais hash bcrypt, exécutez en ligne de commande PHP:
-- php -r "echo password_hash('admin123', PASSWORD_DEFAULT) . PHP_EOL;"
-- php -r "echo password_hash('respo123', PASSWORD_DEFAULT) . PHP_EOL;"
-- php -r "echo password_hash('chauffeur123', PASSWORD_DEFAULT) . PHP_EOL;"
--
-- Ou utilisez un générateur bcrypt en ligne: https://bcrypt-generator.com/
-- 
-- Pour l'instant, ces hash sont des exemples. Remplacez-les par les vrais hash générés.

-- Hash bcrypt pour "admin123" (REMPLACEZ PAR LE VRAI HASH GÉNÉRÉ)
SET @password_admin = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- Hash bcrypt pour "respo123" (REMPLACEZ PAR LE VRAI HASH GÉNÉRÉ)
SET @password_respo = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- Hash bcrypt pour "chauffeur123" (REMPLACEZ PAR LE VRAI HASH GÉNÉRÉ)
SET @password_chauffeur = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- Créer l'admin
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut)
VALUES ('Admin', 'Système', 'admin@mohamme5.ma', @password_admin, '0612345678', 'admin', 'Actif');

-- Créer le responsable bus
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut)
VALUES ('Responsable', 'Bus', 'responsable_bus@mohammed5.ma', @password_respo, '0612345680', 'responsable', 'Actif');

-- Créer le chauffeur
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut)
VALUES ('Chauffeur', 'Test', 'chauffeur@mohammed5.ma', @password_chauffeur, '0612345681', 'chauffeur', 'Actif');

-- Afficher les comptes créés
SELECT id, nom, prenom, email, role, statut 
FROM utilisateurs 
WHERE email IN ('admin@mohamme5.ma', 'responsable_bus@mohammed5.ma', 'chauffeur@mohammed5.ma')
ORDER BY role, nom;

-- NOTE: Si les connexions ne fonctionnent pas, vérifiez que les hash sont corrects.
-- Pour tester avec PHP:
-- <?php
-- $hash = '$2y$10$...'; // Le hash de la base de données
-- var_dump(password_verify('admin123', $hash)); // Doit retourner true

