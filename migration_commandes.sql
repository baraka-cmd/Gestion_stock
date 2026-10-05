ALTER TABLE `commande`
    ADD COLUMN IF NOT EXISTS `etat` ENUM('0', '1') NOT NULL DEFAULT '1'
    AFTER `date_commande`;