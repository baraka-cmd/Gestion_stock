CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(80) NOT NULL,
  `prenom` VARCHAR(80) NOT NULL,
  `email` VARCHAR(254) NOT NULL,
  `telephone` VARCHAR(30) DEFAULT NULL,
  `role` ENUM('admin', 'gestionnaire', 'lecture') NOT NULL DEFAULT 'lecture',
  `actif` TINYINT(1) NOT NULL DEFAULT 1,
  `photo` VARCHAR(255) NOT NULL,
  `date_creation` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_utilisateur_email` (`email`),
  KEY `idx_utilisateur_actif` (`actif`),
  KEY `idx_utilisateur_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;