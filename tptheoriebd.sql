-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 26 fév. 2026 à 09:16
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `tptheoriebd`
--

DELIMITER $$
--
-- Procédures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `CalculerMoyenneEtudiant` (IN `etudiant_id` INT)   BEGIN
    SELECT E.nom, E.prenom, AVG(N.note) AS Moyenne 
    FROM Etudiants E 
    JOIN Inscriptions I ON E.id_etudiant = I.id_etudiant 
    JOIN Notes N ON I.id_insc = N.id_insc 
    WHERE E.id_etudiant = etudiant_id
    GROUP BY E.id_etudiant, E.nom, E.prenom;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `cours`
--

CREATE TABLE `cours` (
  `id_cours` int(11) NOT NULL,
  `nom_cours` varchar(100) NOT NULL,
  `credits` int(11) NOT NULL,
  `id_prof` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `cours`
--

INSERT INTO `cours` (`id_cours`, `nom_cours`, `credits`, `id_prof`) VALUES
(1, 'Base de donn?es', 5, 1),
(2, 'Economie Generale', 4, 2),
(3, 'Resilence', 3, 3),
(4, 'Algorithmique', 5, 1),
(5, 'Etude biblique', 6, 5);

-- --------------------------------------------------------

--
-- Structure de la table `departements`
--

CREATE TABLE `departements` (
  `id_dept` int(11) NOT NULL,
  `nom_dept` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `departements`
--

INSERT INTO `departements` (`id_dept`, `nom_dept`) VALUES
(2, '?conomie'),
(3, 'BTP'),
(1, 'Informatique'),
(5, 'Psychologie'),
(4, 'Theologie');

-- --------------------------------------------------------

--
-- Structure de la table `etudiants`
--

CREATE TABLE `etudiants` (
  `id_etudiant` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `sexe` char(1) DEFAULT NULL CHECK (`sexe` in ('M','F'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `etudiants`
--

INSERT INTO `etudiants` (`id_etudiant`, `nom`, `prenom`, `date_naissance`, `sexe`) VALUES
(1, 'Baraka', 'Ntwali', '2006-07-19', 'M'),
(2, 'Tufurahi', 'Ngaly', '2001-11-20', 'M'),
(3, 'Salama', 'Mwemerankiko', '2003-02-15', 'F'),
(4, 'Irunva', 'Elise', '2000-08-30', 'M'),
(5, 'Uwera', 'Gentille', '2002-01-10', 'F');

-- --------------------------------------------------------

--
-- Structure de la table `inscriptions`
--

CREATE TABLE `inscriptions` (
  `id_insc` int(11) NOT NULL,
  `id_etudiant` int(11) NOT NULL,
  `id_cours` int(11) NOT NULL,
  `date_insc` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `inscriptions`
--

INSERT INTO `inscriptions` (`id_insc`, `id_etudiant`, `id_cours`, `date_insc`) VALUES
(1, 1, 1, '2026-01-15'),
(2, 2, 1, '2026-01-15'),
(3, 3, 2, '2026-01-16'),
(4, 4, 4, '2026-01-17'),
(5, 5, 5, '2026-01-18');

-- --------------------------------------------------------

--
-- Structure de la table `notes`
--

CREATE TABLE `notes` (
  `id_note` int(11) NOT NULL,
  `id_insc` int(11) NOT NULL,
  `note` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `notes`
--

INSERT INTO `notes` (`id_note`, `id_insc`, `note`) VALUES
(1, 1, 15.50),
(2, 2, 12.00),
(3, 3, 18.00),
(4, 4, 9.50),
(5, 5, 14.00),
(100, 1, 15.50);

--
-- Déclencheurs `notes`
--
DELIMITER $$
CREATE TRIGGER `Before_Insert_Note` BEFORE INSERT ON `notes` FOR EACH ROW BEGIN
    IF NEW.note < 0 OR NEW.note > 20 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Erreur : La note doit être comprise entre 0 et 20';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `planning`
--

CREATE TABLE `planning` (
  `id_plan` int(11) NOT NULL,
  `id_cours` int(11) NOT NULL,
  `id_salle` int(11) NOT NULL,
  `date_heure` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `planning`
--

INSERT INTO `planning` (`id_plan`, `id_cours`, `id_salle`, `date_heure`) VALUES
(1, 1, 2, '2026-02-15 08:00:00'),
(2, 2, 1, '2026-02-15 10:00:00'),
(3, 3, 3, '2026-02-16 09:00:00'),
(4, 4, 2, '2026-02-16 11:00:00'),
(5, 5, 1, '2026-02-17 08:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `professeurs`
--

CREATE TABLE `professeurs` (
  `id_prof` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `grade` varchar(50) DEFAULT NULL,
  `departement` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `professeurs`
--

INSERT INTO `professeurs` (`id_prof`, `nom`, `prenom`, `grade`, `departement`) VALUES
(1, 'Kalema', 'Josu?', 'CT', 'Informatique'),
(2, 'Jacques', 'Mulanga', 'Professeur', '?conomie'),
(3, 'Musafiri', 'josue', 'Chef de Travaux', 'Psychologie'),
(4, 'Elias', 'Jean', 'Assistant', 'Informatique'),
(5, 'Theophile', 'Bahati', 'Professeur', 'Theologie');

-- --------------------------------------------------------

--
-- Structure de la table `salles`
--

CREATE TABLE `salles` (
  `id_salle` int(11) NOT NULL,
  `nom_salle` varchar(50) NOT NULL,
  `capacite` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `salles`
--

INSERT INTO `salles` (`id_salle`, `nom_salle`, `capacite`) VALUES
(1, 'B1', 100),
(2, 'B12', 20),
(3, 'A10', 30),
(4, 'A3', 25),
(5, 'B3', 50);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `cours`
--
ALTER TABLE `cours`
  ADD PRIMARY KEY (`id_cours`),
  ADD KEY `fk_prof` (`id_prof`);

--
-- Index pour la table `departements`
--
ALTER TABLE `departements`
  ADD PRIMARY KEY (`id_dept`),
  ADD UNIQUE KEY `nom_dept` (`nom_dept`);

--
-- Index pour la table `etudiants`
--
ALTER TABLE `etudiants`
  ADD PRIMARY KEY (`id_etudiant`);

--
-- Index pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD PRIMARY KEY (`id_insc`),
  ADD KEY `fk_etudiant_insc` (`id_etudiant`),
  ADD KEY `fk_cours_insc` (`id_cours`);

--
-- Index pour la table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id_note`),
  ADD KEY `fk_inscription_note` (`id_insc`);

--
-- Index pour la table `planning`
--
ALTER TABLE `planning`
  ADD PRIMARY KEY (`id_plan`),
  ADD KEY `fk_cours_plan` (`id_cours`),
  ADD KEY `fk_salle_plan` (`id_salle`);

--
-- Index pour la table `professeurs`
--
ALTER TABLE `professeurs`
  ADD PRIMARY KEY (`id_prof`);

--
-- Index pour la table `salles`
--
ALTER TABLE `salles`
  ADD PRIMARY KEY (`id_salle`),
  ADD UNIQUE KEY `nom_salle` (`nom_salle`);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `cours`
--
ALTER TABLE `cours`
  ADD CONSTRAINT `fk_prof` FOREIGN KEY (`id_prof`) REFERENCES `professeurs` (`id_prof`);

--
-- Contraintes pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD CONSTRAINT `fk_cours_insc` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id_cours`),
  ADD CONSTRAINT `fk_etudiant_insc` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiants` (`id_etudiant`);

--
-- Contraintes pour la table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `fk_inscription_note` FOREIGN KEY (`id_insc`) REFERENCES `inscriptions` (`id_insc`);

--
-- Contraintes pour la table `planning`
--
ALTER TABLE `planning`
  ADD CONSTRAINT `fk_cours_plan` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id_cours`),
  ADD CONSTRAINT `fk_salle_plan` FOREIGN KEY (`id_salle`) REFERENCES `salles` (`id_salle`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
