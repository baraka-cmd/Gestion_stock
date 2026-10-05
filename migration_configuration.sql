CREATE TABLE IF NOT EXISTS `configuration` (
  `id` TINYINT UNSIGNED NOT NULL,
  `nom_entreprise` VARCHAR(120) NOT NULL DEFAULT 'D-CLIC Stock',
  `adresse` VARCHAR(255) DEFAULT NULL,
  `telephone` VARCHAR(30) DEFAULT NULL,
  `email` VARCHAR(254) DEFAULT NULL,
  `devise` VARCHAR(8) NOT NULL DEFAULT 'F',
  `seuil_stock_faible` INT UNSIGNED NOT NULL DEFAULT 5,
  `texte_pied_recu` VARCHAR(255) DEFAULT NULL,
  `logo` VARCHAR(255) DEFAULT NULL,
  `date_modification` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `configuration` (`id`)
VALUES (1)
ON DUPLICATE KEY UPDATE `id` = 1;

CREATE TABLE IF NOT EXISTS `journal_configuration` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `configuration_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `ancienne_valeur` LONGTEXT NOT NULL,
  `nouvelle_valeur` LONGTEXT NOT NULL,
  `adresse_ip` VARCHAR(45) DEFAULT NULL,
  `date_modification` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_journal_configuration_date` (`date_modification`),
  CONSTRAINT `fk_journal_configuration` FOREIGN KEY (`configuration_id`)
    REFERENCES `configuration` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;