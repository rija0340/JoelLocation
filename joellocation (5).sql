-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 03 mai 2021 à 12:45
-- Version du serveur :  5.7.31
-- Version de PHP : 7.4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `joellocation`
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
('DoctrineMigrations\\Version20210217171301', '2021-02-17 17:13:08', 110),
('DoctrineMigrations\\Version20210222103900', '2021-02-22 10:39:17', 443);

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
-- Structure de la table `faq`
--

DROP TABLE IF EXISTS `faq`;
CREATE TABLE IF NOT EXISTS `faq` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reponse` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `faq`
--

INSERT INTO `faq` (`id`, `question`, `reponse`) VALUES
(2, 'Comment demander un devis ?', 'En remplissant notre formulaire de contact en ligne ou en nous contactant directement.'),
(3, 'Quelle est la durée d’une journée de location ?', 'La durée d’une journée de location est de 24h.'),
(4, 'Faites-vous de la location de longue durée ?', 'Pour toutes locations de plus de 3 mois, veuillez nous contacter.'),
(5, 'Quels sont les documents nécessaires pour louer ?', 'Il vous faudra votre permis de conduire ainsi que celui des autres conducteurs, carte d’identité ou passeport, et un justificatif de domicile de moins de 3 mois.'),
(6, 'Est-ce que je peux faire une réservation au nom d’une autre personne ?', 'Vous pouvez réserver au nom d’une autre personne, cependant tous les documents nécessaires à la réservation de la location doivent être à son nom.'),
(7, 'Est-ce que le prix de ma réservation peut changer ?', 'Les prix évoluent en permanence. Mais le prix de votre réservation évolue seulement si vous modifiez celle-ci.'),
(8, 'A partir de quel âge puis-je louer une voiture chez Joel Location ?', 'Vous pouvez louer à partir de 21 ans. Il vous faut un permis de conduire de 2 ans minimum valable, voir plus suivant la catégorie de voiture louée.'),
(9, 'Faut-il indiquer son numéro de vol lors de la réservation ?', 'Si nous devons vous livrer votre voiture de location à l’aéroport, vous devez nous communiquer votre numéro de vol. En cas de retard, nous serons informés. Votre voiture de location sera prête à votre arrivée.'),
(10, 'Je veux modifier ma réservation, puis-je le faire ?', 'C’est possible de modifier votre réservation à conditions d’avoir les disponibilités. Le prix de la nouvelle réservation pourrait être plus élevé que la réservation initiale.'),
(11, 'Puis-je ajouter des options supplémentaires ?', 'Vous pouvez ajouter des options supplémentaires et ce jusqu’au début de votre location.'),
(12, 'Puis-je modifier les options souscrites une fois la réservation validée ?', 'Les options validées et réglées ne pourront pas être remboursées mais un échange contre un autre est possible.'),
(13, 'Proposez-vous la location de sièges enfants ?', 'Oui, des sièges enfants sont possible en option.'),
(14, 'A quel moment vous informer si je souhaite annuler ma réservation ?', 'Vous devez nous informer le plus tôt possible, idéalement par téléphone suivi d’une confirmation par écrit par courriel.'),
(15, 'Comment annuler ma réservation ?', 'Pour annuler votre réservation, vous pouvez le faire en nous contactant. Une confirmation écrite sera nécessaire. Un justificatif pourrait vous êtes demandé dans certains cas.'),
(16, 'Quand régler le reste du solde de la location ?', 'Vous devez régler la totalité avant le début de la location. Si vous souhaitez nous payer par virement, ceci doit se faire au moins 5 jours ouvrés avant le début de location.'),
(17, 'Quels sont les moyens de paiement acceptés ?', 'Joel Location accepte les cartes Mastercard et Visa, les chèques, chèques vacances, espèces et virements. À la suite de plusieurs abus, nous encourageons vivement d’autres moyens de paiement mis à votre disposition que les chèques. Les chèques étrangers ne sont pas acceptés.'),
(18, 'Puis-je louer un véhicule sans mon permis ?', 'Non, il nous faudra au moins une photocopie de votre permis.'),
(19, 'J’arrive à l’aéroport de Pointe-à-Pitre. Où est ce que ma voiture sera livrée ?', 'Nous vous attendrons à la sortie bagages avec une pancarte à votre nom. La voiture se trouvera sur le parking de l’aéroport, juste en face.'),
(20, 'Est-ce je peux récupérer ou livrer le véhicule en dehors des heures d’ouverture ?', 'C’est possible en dehors des heures d’ouverture mais des frais supplémentaires peuvent être appliqués.'),
(21, 'Est-ce qu’il faut verser une caution ?', 'La caution est obligatoire. Elle permet de couvrir les frais en cas de dégradations sur le véhicule.'),
(22, 'Quel est le montant de la caution ?', 'La caution est à partir de 700€. Elle varie en fonction de la catégorie du véhicule.'),
(23, 'Je souhaiterais partir quelques jours à Marie-Galante durant mon séjour. Est-ce que je pourrais emmener mon véhicule ?', 'Si vous souhaitez conduire le véhicule hors de la Guadeloupe continentale, vous devrez solliciter notre accord préalable.'),
(24, 'Est-ce que la voiture de location peut être conduite par une autre personne ?', 'Un autre conducteur peut être autorisé à conduire à condition d’être déclaré comme conducteur supplémentaire sur le contrat de location.'),
(25, 'Est-ce que je peux fumer dans une voiture de location ?', 'Il est interdit de fumer dans les véhicules de location.'),
(26, 'Le véhicule a un problème mécanique, à qui dois-je m’adresser ?', 'Vous devez nous contacter immédiatement pour tout problème.'),
(27, 'Est-ce que je peux réparer moi-même mon véhicule de location ?', 'Toute réparation ou service quelconque effectuée sans l’accord de Joel Location est interdite et entrainera une perte totale de la caution.'),
(28, 'Où se trouve le constat d’accident ?', 'Il se trouve dans la boite à gants.'),
(29, 'Est-ce que la caution est débitée ?', 'Sans dommage ou d’éléments manquants sur le véhicule, la caution n’est pas débitée.'),
(30, 'Quand est-ce que ma caution est annulée ?', 'La caution est annulée à la fin du contrat, sans dommage sur le véhicule et sous réserve de vérification supplémentaire.'),
(31, 'Qui est responsable des infractions pendant la location ?', 'Vous êtes entièrement responsable de toutes infractions au code de la route, et aussi des amendes de stationnement et frais de parkings privés pendant votre location.'),
(32, 'Je remets le véhicule en retard, puis-je éviter une facturation supplémentaire ?', 'Vous disposez d’une (1) heure (« période de grâce ») une fois l’heure de restitution passée pour nous remettre le véhicule, sans qu’une journée supplémentaire vous soit facturée, sous pénalité après comptabilisée comme une journée supplémentaire.'),
(33, 'Puis-je bénéficier d’un délai de courtoisie lors de mon retour ?', 'Vous disposez d’un délai d’1 heure.'),
(34, 'Comment prolonger mon contrat de location ?', 'Vous pouvez prolonger votre contrat de location en nous informons 48 heures avant la fin de celle-ci, et sous condition de disponibilité.'),
(35, 'Quelle procédure respecter pour remettre la voiture ?', 'Vérifier l’état intérieur et extérieur du véhicule et signaler tout dommage. Faire le plein et nettoyer l’intérieur et l’extérieur.'),
(36, 'Pourquoi suis-je facturé un nettoyage spécial ?', 'Un nettoyage spécial demande du temps et des moyens supplémentaires pour remettre le véhicule en état pour une location.'),
(37, 'Dois-je rendre le véhicule avec un plein de carburant ?', 'Le véhicule est loué avec un plein et doit être rendu avec un plein.'),
(38, 'Pourquoi le montant de ma facture est plus élevé que lors de ma réservation ?', 'Votre facture peut être plus élevée que la réservation si vous avez pris des options supplémentaires après la réservation.'),
(39, 'Puis-je modifier l’adresse de facturation ?', 'Vous pouvez le faire en nous contactant directement par courriel et en nous précisant votre numéro de contrat/devis.'),
(40, 'Où sont les conditions générales de location ?', 'Vous pouvez consulter les conditions générales de location sur notre site internet, ou demander en avance qu’une copie vous soit fournie avec votre contrat de location.');

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
  `date_reservation` datetime NOT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `lieu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_reservation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_42C8495519EB6921` (`client_id`),
  KEY `IDX_42C849554A4A3511` (`vehicule_id`),
  KEY `IDX_42C84955FB88E14F` (`utilisateur_id`),
  KEY `IDX_42C849556776468B` (`mode_reservation_id`),
  KEY `IDX_42C8495514237FB` (`etat_reservation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `reservation`
--

INSERT INTO `reservation` (`id`, `client_id`, `vehicule_id`, `utilisateur_id`, `mode_reservation_id`, `etat_reservation_id`, `type`, `date_reservation`, `date_debut`, `date_fin`, `lieu`, `code_reservation`) VALUES
(1, 1, 1, 1, 3, 1, 'VACANCE D\'ETE', '2021-02-05 00:00:00', '2021-04-09 14:00:00', '2021-04-19 06:00:00', 'GUADELOUPE', '15426985'),
(2, 1, 1, 1, 3, 1, 'mariage', '2021-02-08 00:00:00', '2021-04-10 05:00:00', '2021-04-15 21:30:00', 'guadeloupe', '123'),
(3, 1, 1, 1, 3, 1, 'mariage', '2021-02-21 00:00:00', '2021-04-22 00:00:00', '2021-04-26 00:00:00', 'guadeloupe', '123'),
(4, 1, 1, 1, 3, 1, 'famille', '2021-04-27 00:00:00', '2021-04-25 00:00:00', '2021-04-27 00:00:00', 'guadeloupe', '123'),
(5, 1, 1, 1, 1, 2, 'test', '2021-05-01 00:00:00', '2021-05-01 00:00:00', '2021-05-01 00:00:00', 'test', '1223'),
(6, 5, 2, 5, 2, 3, 'voyage d\'étude', '2021-05-01 00:00:00', '2021-05-13 00:00:00', '2021-05-29 00:00:00', 'guadeloupe', '123'),
(8, 1, 3, 1, 1, 2, 'test time', '2021-05-02 00:00:00', '2021-05-02 00:00:00', '2021-05-14 00:00:00', 'guadeloupe', 'test'),
(9, 5, 2, 5, 2, 3, 'test real time', '2021-05-02 16:42:00', '2021-05-02 16:42:00', '2021-05-09 16:42:00', 'guadeloupe', '1223');

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `username`, `roles`, `password`, `nom`, `prenom`, `adresse`, `mail`, `telephone`, `portable`, `presence`, `date_inscription`) VALUES
(1, 'teste', '[\"ROLE_CLIENT\"]', 'test', 'teste', 'teste', 'teste', 'teste', 'teste', '032 50 895 63', 1, '2021-01-17'),
(2, '123', '[\"ROLE_CLIENT\"]', '$argon2id$v=19$m=65536,t=4,p=1$VEt2cWJKakdpUkZ1YXZlQg$MnYhCdFnmv4dy/tRl9aYjnkr24BDxcih6LWQSsky9Cg', '123', '123', '123', '123@123.123', '123', '123', 1, '2021-02-08'),
(4, 'admin', '[\"ROLE_SUPER_ADMIN\"]', '$argon2id$v=19$m=65536,t=4,p=1$TnVwZzNyV1N3dHBReVpQcA$d+LZTV3kg8qu+ZB7/MiCwlPnIlgza3ILpbwA3JMNnEc', 'admin', 'admin', 'admin', 'admin', 'admin', 'admin', 1, '2021-02-17'),
(5, 'raberia', '[\"ROLE_PERSONNEL\"]', 'izaho iany', 'RAKOTOARINELINA', 'Rija andrianandrianina', 'toby ratsimandrava', 'rija0340@gmail.com', '032 50 721 83', '034 78 765 48', 1, '2021-04-30'),
(6, 'tset', '[\"ROLE_PERSONNEL\"]', 'resrser', 'tset', 'test', 'test', 'rakotoarnelina@gmail.com', '0325070123', '0347815964', 1, '2021-05-01');

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
  `details` longtext COLLATE utf8mb4_unicode_ci,
  `carburation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caution` double DEFAULT NULL,
  `vitesse` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bagages` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `portes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passagers` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `atouts` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_292FFF1D4827B9B2` (`marque_id`),
  KEY `IDX_292FFF1DC54C8C93` (`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `vehicule`
--

INSERT INTO `vehicule` (`id`, `marque_id`, `type_id`, `immatriculation`, `date_mise_service`, `date_mise_location`, `modele`, `prix_acquisition`, `tarif_journaliere`, `details`, `carburation`, `caution`, `vitesse`, `bagages`, `portes`, `passagers`, `atouts`, `image`, `updated_at`) VALUES
(1, 1, 1, '1111111111', '2021-01-01', '2021-01-01', 'CLIO', 1500, 150, 'Si vous recherchez une voiture qui offre un rapport qualité / prix incroyable, alors la Clio IV, avec sa grande polyvalence, est faite pour vous. Cette voiture de tourisme vous garantit un super confort aussi bien sur les petites rues que sur les grandes routes. Elle a une consommation de carburant raisonnable, et est fiable.', 'ESSENCE', 700, 'Manuelle', '3', '5', '5', 'autoradio, climatisation, fermeture centralisée', '659dcc6378f54773bb75cc43e8d97488.png', NULL),
(2, 1, 1, '222222222', '2016-01-01', '2016-01-01', 'TWINGO', 150, 150, 'Découvrir les villes de la Guadeloupe n’a jamais été aussi simple avec Renault Twingo. C’est aussi une voiture à faible consommation de carburant qui peut vous emmener où vous voulez confortablement.\r\n\r\nCette petite voiture de ﬁtness est proposée dans une gamme de prix pratique et abordable. Le fait de pouvoir facilement la garer n’importe où dans la ville est un énorme bonus.', 'Essence', 700, 'Manuelle', '1', '5', '4', 'autoradio, climatisation, fermeture centralisée', 'a87d84350ce9b16e658b3c3cc935e1e4.png', NULL),
(3, 1, 1, '333333333333', '2016-01-01', '2016-01-01', 'CAPTUR', 1500, 150, 'Avec le Renault Captur, vous pouvez parcourir des kilomètres en Guadeloupe. Cette voiture est idéale pour tous vos déplacements touristiques en famille et entre amis avec suﬃsamment de place pour tout le monde. Quel que soit l’itinéraire que vous empruntiez, entre les villes de Grande-Terre ou de Basse-Terre, notre Renault Captur est le choix parfait pour des promenades sereines.', 'Essence', 800, 'Manuelle', '3', '5', '5', 'autoradio, climatisation, fermeture centralisée', '223e85b39f37821b7c8eeafbdbfa1d96.png', NULL);

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
