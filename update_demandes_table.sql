-- Script pour mettre à jour la table demandes pour supporter les nouveaux types de demandes
-- Exécutez ce script dans votre base de données MySQL

USE transport_scolaire;

-- Option 1: Modifier l'ENUM pour ajouter les nouveaux types
-- Note: MySQL ne permet pas de modifier un ENUM directement, on doit utiliser ALTER TABLE avec MODIFY
ALTER TABLE demandes 
MODIFY COLUMN type_demande ENUM(
    'inscription', 
    'modification', 
    'desinscription',
    'Augmentation',
    'Congé',
    'Déménagement',
    'Autre'
) NOT NULL;

-- Option 2: Si vous préférez utiliser VARCHAR pour plus de flexibilité (décommentez cette partie et commentez Option 1)
-- ALTER TABLE demandes 
-- MODIFY COLUMN type_demande VARCHAR(50) NOT NULL;

