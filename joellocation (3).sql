-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le :  lun. 22 fév. 2021 à 08:05
-- Version du serveur :  10.4.10-MariaDB
-- Version de PHP :  7.3.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `joellocation`
--

-- --------------------------------------------------------

--
-- Structure de la table `agence`
--

DROP TABLE IF EXISTS `agence`;
CREATE TABLE IF NOT EXISTS `agence` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `presentation` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `mail` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

DROP TABLE IF EXISTS `avis`;
CREATE TABLE IF NOT EXISTS `avis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reservation_id` int(11) NOT NULL,
  `global` int(11) NOT NULL,
  `ponctualite` int(11) NOT NULL,
  `accueil` int(11) NOT NULL,
  `service` int(11) NOT NULL,
  `commentaire` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8F91ABF0B83297E7` (`reservation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `contact`
--

DROP TABLE IF EXISTS `contact`;
CREATE TABLE IF NOT EXISTS `contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mail` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE IF NOT EXISTS `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20201225160351', '2020-12-25 16:07:15', 227),
('DoctrineMigrations\\Version20201226060224', '2020-12-26 06:02:48', 379),
('DoctrineMigrations\\Version20201226061509', '2020-12-26 06:15:28', 2181),
('DoctrineMigrations\\Version20201226063149', '2020-12-26 06:32:14', 2576),
('DoctrineMigrations\\Version20201226064815', '2020-12-26 06:48:44', 4479),
('DoctrineMigrations\\Version20201226070756', '2020-12-26 07:08:05', 4427),
('DoctrineMigrations\\Version20210217171200', '2021-02-17 17:12:11', 149),
('DoctrineMigrations\\Version20210217171301', '2021-02-17 17:13:08', 110);

-- --------------------------------------------------------

--
-- Structure de la table `etat_reservation`
--

DROP TABLE IF EXISTS `etat_reservation`;
CREATE TABLE IF NOT EXISTS `etat_reservation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `etat_reservation`
--

INSERT INTO `etat_reservation` (`id`, `libelle`) VALUES
(1, 'EN ATTENTE'),
(2, 'EN COURS'),
(3, 'TERMINER');

-- --------------------------------------------------------

--
-- Structure de la table `marque`
--

DROP TABLE IF EXISTS `marque`;
CREATE TABLE IF NOT EXISTS `marque` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `marque`
--

INSERT INTO `marque` (`id`, `libelle`) VALUES
(1, 'RENAULT');

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

DROP TABLE IF EXISTS `message`;
CREATE TABLE IF NOT EXISTS `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `message` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_B6BD307F19EB6921` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `mode_paiement`
--

DROP TABLE IF EXISTS `mode_paiement`;
CREATE TABLE IF NOT EXISTS `mode_paiement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `mode_paiement`
--

INSERT INTO `mode_paiement` (`id`, `libelle`) VALUES
(1, 'CARTE BANCAIRE'),
(2, 'ESPECE');

-- --------------------------------------------------------

--
-- Structure de la table `mode_reservation`
--

DROP TABLE IF EXISTS `mode_reservation`;
CREATE TABLE IF NOT EXISTS `mode_reservation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `mode_reservation`
--

INSERT INTO `mode_reservation` (`id`, `libelle`) VALUES
(1, 'TELEPHONE'),
(2, 'EMAIL'),
(3, 'WEB');

-- --------------------------------------------------------

--
-- Structure de la table `paiement`
--

DROP TABLE IF EXISTS `paiement`;
CREATE TABLE IF NOT EXISTS `paiement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reservation_id` int(11) NOT NULL,
  `mode_paiement_id` int(11) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `montant` int(11) NOT NULL,
  `date_paiement` date NOT NULL,
  `motif` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_B1DC7A1EB83297E7` (`reservation_id`),
  KEY `IDX_B1DC7A1E438F5B63` (`mode_paiement_id`),
  KEY `IDX_B1DC7A1EFB88E14F` (`utilisateur_id`),
  KEY `IDX_B1DC7A1E19EB6921` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `paiement`
--

INSERT INTO `paiement` (`id`, `reservation_id`, `mode_paiement_id`, `utilisateur_id`, `client_id`, `montant`, `date_paiement`, `motif`) VALUES
(1, 3, 1, 1, 1, 150, '2021-02-22', 'caution pour le véhicule RENAULT CLIO');

-- --------------------------------------------------------

--
-- Structure de la table `reservation`
--

DROP TABLE IF EXISTS `reservation`;
CREATE TABLE IF NOT EXISTS `reservation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `vehicule_id` int(11) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `mode_reservation_id` int(11) NOT NULL,
  `etat_reservation_id` int(11) NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_reservation` date NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `lieu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_reservation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_42C8495519EB6921` (`client_id`),
  KEY `IDX_42C849554A4A3511` (`vehicule_id`),
  KEY `IDX_42C84955FB88E14F` (`utilisateur_id`),
  KEY `IDX_42C849556776468B` (`mode_reservation_id`),
  KEY `IDX_42C8495514237FB` (`etat_reservation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `reservation`
--

INSERT INTO `reservation` (`id`, `client_id`, `vehicule_id`, `utilisateur_id`, `mode_reservation_id`, `etat_reservation_id`, `type`, `date_reservation`, `date_debut`, `date_fin`, `lieu`, `code_reservation`) VALUES
(1, 1, 1, 1, 3, 1, 'VACANCE D\'ETE', '2021-02-05', '2021-02-09', '2021-02-09', 'GUADELOUPE', '15426985'),
(2, 1, 1, 1, 3, 1, 'mariage', '2021-02-08', '2021-02-10', '2021-02-15', 'guadeloupe', '123'),
(3, 1, 1, 1, 3, 1, 'mariage', '2021-02-21', '2021-02-22', '2021-03-03', 'guadeloupe', '123');

-- --------------------------------------------------------

--
-- Structure de la table `type`
--

DROP TABLE IF EXISTS `type`;
CREATE TABLE IF NOT EXISTS `type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `type`
--

INSERT INTO `type` (`id`, `libelle`) VALUES
(1, 'BERLINE');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:json)',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mail` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `portable` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `presence` tinyint(1) NOT NULL,
  `date_inscription` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649F85E0677` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `username`, `roles`, `password`, `nom`, `prenom`, `adresse`, `mail`, `telephone`, `portable`, `presence`, `date_inscription`) VALUES
(1, 'teste', '[\"ROLE_CLIENT\"]', '$argon2id$v=19$m=65536,t=4,p=1$bUtscDVDd1hEcS5DWlpxOQ$L0njAkNiFDqDUdZBVarzPZTxNHgW7SJyhvgfHwXWJKA', 'teste', 'teste', 'teste', 'teste', 'teste', 'teste', 1, '2021-01-17'),
(2, '123', '[\"ROLE_CLIENT\"]', '$argon2id$v=19$m=65536,t=4,p=1$VEt2cWJKakdpUkZ1YXZlQg$MnYhCdFnmv4dy/tRl9aYjnkr24BDxcih6LWQSsky9Cg', '123', '123', '123', '123@123.123', '123', '123', 1, '2021-02-08'),
(4, 'admin', '[\"ROLE_SUPER_ADMIN\"]', '$argon2id$v=19$m=65536,t=4,p=1$TnVwZzNyV1N3dHBReVpQcA$d+LZTV3kg8qu+ZB7/MiCwlPnIlgza3ILpbwA3JMNnEc', 'admin', 'admin', 'admin', 'admin', 'admin', 'admin', 1, '2021-02-17');

-- --------------------------------------------------------

--
-- Structure de la table `vehicule`
--

DROP TABLE IF EXISTS `vehicule`;
CREATE TABLE IF NOT EXISTS `vehicule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marque_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `immatriculation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_mise_service` date DEFAULT NULL,
  `date_mise_location` date NOT NULL,
  `modele` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prix_acquisition` int(11) DEFAULT NULL,
  `tarif_journaliere` int(11) NOT NULL,
  `details` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `carburation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caution` double DEFAULT NULL,
  `vitesse` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bagages` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `portes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passagers` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `atouts` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_292FFF1D4827B9B2` (`marque_id`),
  KEY `IDX_292FFF1DC54C8C93` (`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `vehicule`
--

INSERT INTO `vehicule` (`id`, `marque_id`, `type_id`, `immatriculation`, `date_mise_service`, `date_mise_location`, `modele`, `prix_acquisition`, `tarif_journaliere`, `details`, `carburation`, `caution`, `vitesse`, `bagages`, `portes`, `passagers`, `atouts`, `image`) VALUES
(1, 1, 1, '1111111111', '2021-01-01', '2021-01-01', 'CLIO', 1500, 150, 'Si vous recherchez une voiture qui offre un rapport qualité / prix incroyable, alors la Clio IV, avec sa grande polyvalence, est faite pour vous. Cette voiture de tourisme vous garantit un super confort aussi bien sur les petites rues que sur les grandes routes. Elle a une consommation de carburant raisonnable, et est fiable.', 'ESSENCE', 700, 'Manuelle', '3', '5', '5', 'autoradio, climatisation, fermeture centralisée', '659dcc6378f54773bb75cc43e8d97488.png'),
(2, 1, 1, '222222222', '2016-01-01', '2016-01-01', 'TWINGO', 150, 150, 'Découvrir les villes de la Guadeloupe n’a jamais été aussi simple avec Renault Twingo. C’est aussi une voiture à faible consommation de carburant qui peut vous emmener où vous voulez confortablement.\r\n\r\nCette petite voiture de ﬁtness est proposée dans une gamme de prix pratique et abordable. Le fait de pouvoir facilement la garer n’importe où dans la ville est un énorme bonus.', 'Essence', 700, 'Manuelle', '1', '5', '4', 'autoradio, climatisation, fermeture centralisée', 'a87d84350ce9b16e658b3c3cc935e1e4.png'),
(3, 1, 1, '333333333333', '2016-01-01', '2016-01-01', 'CAPTUR', 1500, 150, 'Avec le Renault Captur, vous pouvez parcourir des kilomètres en Guadeloupe. Cette voiture est idéale pour tous vos déplacements touristiques en famille et entre amis avec suﬃsamment de place pour tout le monde. Quel que soit l’itinéraire que vous empruntiez, entre les villes de Grande-Terre ou de Basse-Terre, notre Renault Captur est le choix parfait pour des promenades sereines.', 'Essence', 800, 'Manuelle', '3', '5', '5', 'autoradio, climatisation, fermeture centralisée', '223e85b39f37821b7c8eeafbdbfa1d96.png');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `FK_8F91ABF0B83297E7` FOREIGN KEY (`reservation_id`) REFERENCES `reservation` (`id`);

--
-- Contraintes pour la table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `FK_B6BD307F19EB6921` FOREIGN KEY (`client_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `paiement`
--
ALTER TABLE `paiement`
  ADD CONSTRAINT `FK_B1DC7A1E19EB6921` FOREIGN KEY (`client_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_B1DC7A1E438F5B63` FOREIGN KEY (`mode_paiement_id`) REFERENCES `mode_paiement` (`id`),
  ADD CONSTRAINT `FK_B1DC7A1EB83297E7` FOREIGN KEY (`reservation_id`) REFERENCES `reservation` (`id`),
  ADD CONSTRAINT `FK_B1DC7A1EFB88E14F` FOREIGN KEY (`utilisateur_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `FK_42C8495514237FB` FOREIGN KEY (`etat_reservation_id`) REFERENCES `etat_reservation` (`id`),
  ADD CONSTRAINT `FK_42C8495519EB6921` FOREIGN KEY (`client_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_42C849554A4A3511` FOREIGN KEY (`vehicule_id`) REFERENCES `vehicule` (`id`),
  ADD CONSTRAINT `FK_42C849556776468B` FOREIGN KEY (`mode_reservation_id`) REFERENCES `mode_reservation` (`id`),
  ADD CONSTRAINT `FK_42C84955FB88E14F` FOREIGN KEY (`utilisateur_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `vehicule`
--
ALTER TABLE `vehicule`
  ADD CONSTRAINT `FK_292FFF1D4827B9B2` FOREIGN KEY (`marque_id`) REFERENCES `marque` (`id`),
  ADD CONSTRAINT `FK_292FFF1DC54C8C93` FOREIGN KEY (`type_id`) REFERENCES `type` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
