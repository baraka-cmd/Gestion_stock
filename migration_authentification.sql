ALTER TABLE `utilisateur`
  ADD COLUMN IF NOT EXISTS `mot_de_passe_hash` VARCHAR(255) DEFAULT NULL AFTER `email`,
  ADD COLUMN IF NOT EXISTS `session_version` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `actif`;

CREATE TABLE IF NOT EXISTS `auth_configuration` (
  `id` TINYINT UNSIGNED NOT NULL,
  `admin_initialise` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `auth_configuration` (`id`, `admin_initialise`)
VALUES (1, 0)
ON DUPLICATE KEY UPDATE `id` = 1;

CREATE TABLE IF NOT EXISTS `auth_tentative_connexion` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email_hash` CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `ip_hash` CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `tente_le` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_auth_email_date` (`email_hash`, `tente_le`),
  KEY `idx_auth_ip_date` (`ip_hash`, `tente_le`),
  KEY `idx_auth_tentative_date` (`tente_le`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;