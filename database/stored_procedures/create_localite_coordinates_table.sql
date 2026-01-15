-- =====================================================
-- Script SQL pour créer la table localite_coordinates
-- =====================================================

CREATE TABLE IF NOT EXISTS `localite_coordinates` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code_pays` VARCHAR(3) NOT NULL COMMENT 'Code pays (alpha3)',
    `code_localite` VARCHAR(20) NOT NULL COMMENT 'Code de la localité',
    `niveau` INT NOT NULL COMMENT 'Niveau administratif (1, 2, 3)',
    `latitude` DECIMAL(10, 8) NOT NULL COMMENT 'Latitude du centroïde',
    `longitude` DECIMAL(11, 8) NOT NULL COMMENT 'Longitude du centroïde',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_localite_coords` (`code_pays`, `code_localite`, `niveau`),
    KEY `localite_coordinates_code_pays_index` (`code_pays`),
    KEY `localite_coordinates_code_localite_index` (`code_localite`),
    KEY `localite_coordinates_code_pays_niveau_index` (`code_pays`, `niveau`)
) 