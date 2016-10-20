-- MovieContentFilter (https://www.moviecontentfilter.com/)
-- Copyright (c) delight.im (https://www.delight.im/)
-- Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

INSERT INTO `categories` (`id`, `name`, `label`, `is_general`, `topic_id`) VALUES
(1, 'commercial', 'Commercial content', 1, 1),
(2, 'advertBreak', 'Advert break', 0, 1),
(3, 'consumerism', 'Consumerism', 0, 1),
(4, 'productPlacement', 'Product placement', 0, 1),
(5, 'discrimination', 'Discrimination', 1, 2),
(6, 'adultism', 'Adultism', 0, 2),
(7, 'antisemitism', 'Antisemitism', 0, 2),
(8, 'genderism', 'Genderism', 0, 2),
(9, 'homophobia', 'Homophobia', 0, 2),
(10, 'misandry', 'Misandry', 0, 2),
(11, 'misogyny', 'Misogyny', 0, 2),
(12, 'racism', 'Racism', 0, 2),
(13, 'sexism', 'Sexism', 0, 2),
(14, 'supremacism', 'Supremacism', 0, 2),
(15, 'transphobia', 'Transphobia', 0, 2),
(16, 'xenophobia', 'Xenophobia', 0, 2),
(17, 'dispensable', 'Dispensable scenes', 1, 3),
(18, 'idiocy', 'Idiocy', 0, 3),
(19, 'tedious', 'Tedious scene', 0, 3),
(20, 'drugs', 'Drugs', 1, 4),
(21, 'alcohol', 'Alcohol', 0, 4),
(22, 'antipsychotics', 'Antipsychotics', 0, 4),
(23, 'cigarettes', 'Cigarettes', 0, 4),
(24, 'depressants', 'Depressants', 0, 4),
(25, 'gambling', 'Gambling', 0, 4),
(26, 'hallucinogens', 'Hallucinogens', 0, 4),
(27, 'stimulants', 'Stimulants', 0, 4),
(28, 'fear', 'Fear', 1, 5),
(29, 'accident', 'Accident', 0, 5),
(30, 'acrophobia', 'Acrophobia', 0, 5),
(31, 'aliens', 'Aliens', 0, 5),
(32, 'arachnophobia', 'Arachnophobia', 0, 5),
(33, 'astraphobia', 'Astraphobia', 0, 5),
(34, 'aviophobia', 'Aviophobia', 0, 5),
(35, 'chemophobia', 'Chemophobia', 0, 5),
(36, 'claustrophobia', 'Claustrophobia', 0, 5),
(37, 'coulrophobia', 'Coulrophobia', 0, 5),
(38, 'cynophobia', 'Cynophobia', 0, 5),
(39, 'death', 'Death', 0, 5),
(40, 'dentophobia', 'Dentophobia', 0, 5),
(41, 'emetophobia', 'Emetophobia', 0, 5),
(42, 'enochlophobia', 'Enochlophobia', 0, 5),
(43, 'explosion', 'Explosion', 0, 5),
(44, 'fire', 'Fire', 0, 5),
(45, 'gerascophobia', 'Gerascophobia', 0, 5),
(46, 'ghosts', 'Ghosts', 0, 5),
(47, 'grave', 'Grave', 0, 5),
(48, 'hemophobia', 'Hemophobia', 0, 5),
(49, 'hylophobia', 'Hylophobia', 0, 5),
(50, 'melissophobia', 'Melissophobia', 0, 5),
(51, 'misophonia', 'Misophonia', 0, 5),
(52, 'musophobia', 'Musophobia', 0, 5),
(53, 'mysophobia', 'Mysophobia', 0, 5),
(54, 'nosocomephobia', 'Nosocomephobia', 0, 5),
(55, 'nyctophobia', 'Nyctophobia', 0, 5),
(56, 'siderodromophobia', 'Siderodromophobia', 0, 5),
(57, 'thalassophobia', 'Thalassophobia', 0, 5),
(58, 'vampires', 'Vampires', 0, 5),
(59, 'language', 'Language', 1, 6),
(60, 'blasphemy', 'Blasphemy', 0, 6),
(61, 'nameCalling', 'Name-calling', 0, 6),
(62, 'sexualDialogue', 'Sexual dialogue', 0, 6),
(63, 'swearing', 'Swearing', 0, 6),
(64, 'vulgarity', 'Vulgarity', 0, 6),
(65, 'nudity', 'Nudity', 1, 7),
(66, 'bareButtocks', 'Bare buttocks', 0, 7),
(67, 'exposedGenitalia', 'Exposed genitalia', 0, 7),
(68, 'fullNudity', 'Full nudity', 0, 7),
(69, 'toplessness', 'Toplessness', 0, 7),
(70, 'sex', 'Sex', 1, 8),
(71, 'adultery', 'Adultery', 0, 8),
(72, 'analSex', 'Anal sex', 0, 8),
(73, 'coitus', 'Coitus', 0, 8),
(74, 'kissing', 'Kissing', 0, 8),
(75, 'masturbation', 'Masturbation', 0, 8),
(76, 'objectification', 'Objectification', 0, 8),
(77, 'oralSex', 'Oral sex', 0, 8),
(78, 'premaritalSex', 'Premarital sex', 0, 8),
(79, 'promiscuity', 'Promiscuity', 0, 8),
(80, 'prostitution', 'Prostitution', 0, 8),
(81, 'violence', 'Violence', 1, 9),
(82, 'choking', 'Choking', 0, 9),
(83, 'crueltyToAnimals', 'Cruelty to animals', 0, 9),
(84, 'culturalViolence', 'Cultural violence', 0, 9),
(85, 'desecration', 'Desecration', 0, 9),
(86, 'emotionalViolence', 'Emotional violence', 0, 9),
(87, 'kicking', 'Kicking', 0, 9),
(88, 'massacre', 'Massacre', 0, 9),
(89, 'murder', 'Murder', 0, 9),
(90, 'punching', 'Punching', 0, 9),
(91, 'rape', 'Rape', 0, 9),
(92, 'slapping', 'Slapping', 0, 9),
(93, 'slavery', 'Slavery', 0, 9),
(94, 'stabbing', 'Stabbing', 0, 9),
(95, 'torture', 'Torture', 0, 9),
(96, 'warfare', 'Warfare', 0, 9),
(97, 'weapons', 'Weapons', 0, 9);

INSERT INTO `channels` (`id`, `name`, `label`, `is_default`) VALUES
(1, 'both', 'Both video and audio', 1),
(2, 'video', 'Video only', 0),
(3, 'audio', 'Audio only', 0);

INSERT INTO `severities` (`id`, `name`, `label`, `available_as_annotation`, `available_as_preference`, `label_in_preferences`, `inclusiveness`, `coefficient`) VALUES
(1, 'low', 'Low', 1, 1, 'Filter low, medium and high severity', 3, '0.250'),
(2, 'medium', 'Medium', 1, 1, 'Filter medium and high severity', 2, '1.000'),
(3, 'high', 'High', 1, 1, 'Filter high severity', 1, '1.750'),
(4, 'none', 'None', 0, 1, 'Do not filter anything', 0, '0.000');

INSERT INTO `topics` (`id`, `label`) VALUES
(1, 'Commercial content'),
(2, 'Discrimination'),
(3, 'Dispensable scenes'),
(4, 'Drugs'),
(5, 'Fear'),
(6, 'Language'),
(7, 'Nudity'),
(8, 'Sex'),
(9, 'Violence');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
