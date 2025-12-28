-- Script pour créer/corriger les comptes de test admin et responsable
-- À exécuter si les identifiants ne fonctionnent pas

USE transport_scolaire;

-- Vérifier et mettre à jour le hash du mot de passe pour test123
-- Hash bcrypt pour "test123": $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

-- Hash bcrypt pour "test123"
SET @password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- Supprimer les comptes existants pour éviter les doublons
DELETE FROM utilisateurs WHERE email = 'admin@transport.ma' AND role = 'admin';
DELETE FROM utilisateurs WHERE email = 'nadia.kettani@transport.ma' AND role = 'responsable';
DELETE FROM utilisateurs WHERE email = 'omar.benjelloun@transport.ma' AND role = 'responsable';

-- Créer l'admin
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut)
VALUES ('Admin', 'Système', 'admin@transport.ma', @password_hash, '0612345678', 'admin', 'Actif');

-- Créer les responsables
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut)
VALUES 
    ('Kettani', 'Nadia', 'nadia.kettani@transport.ma', @password_hash, '0612345685', 'responsable', 'Actif'),
    ('Benjelloun', 'Omar', 'omar.benjelloun@transport.ma', @password_hash, '0612345686', 'responsable', 'Actif');

-- Afficher les comptes créés
SELECT id, nom, prenom, email, role, statut 
FROM utilisateurs 
WHERE email IN ('admin@transport.ma', 'nadia.kettani@transport.ma', 'omar.benjelloun@transport.ma')
ORDER BY role, nom;

