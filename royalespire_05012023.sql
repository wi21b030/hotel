-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 05. Jan 2023 um 19:40
-- Server-Version: 10.4.27-MariaDB
-- PHP-Version: 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `royalespire`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(260) NOT NULL,
  `uploadtime` int(50) NOT NULL,
  `text` text NOT NULL,
  `path` varchar(260) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Daten für Tabelle `news`
--

INSERT INTO `news` (`id`, `title`, `uploadtime`, `text`, `path`) VALUES
(52, 'Die besten Restaurants', 1672869281, 'Unsere Geheimtipps!', 'uploads/news/pic/Die besten Restaurants.jpg'),
(53, 'Die besten Bars von Wien', 1672870126, 'Hier die Top Bars!', 'uploads/news/pic/Die besten Bars von Wien.jpg'),
(54, 'Die besten Sightseeing-Orte', 1672937376, 'Die Top 10!', 'uploads/news/pic/Die besten Sightseeing-Orte.jpg');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `reservation`
--

CREATE TABLE `reservation` (
  `id` int(11) NOT NULL,
  `checkin` date NOT NULL,
  `checkout` date NOT NULL,
  `breakfast` tinyint(1) NOT NULL,
  `parking` tinyint(1) NOT NULL,
  `pet` tinyint(1) NOT NULL,
  `users_username` varchar(80) NOT NULL,
  `time` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Daten für Tabelle `reservation`
--

INSERT INTO `reservation` (`id`, `checkin`, `checkout`, `breakfast`, `parking`, `pet`, `users_username`, `time`, `user_id`) VALUES
(1, '2022-12-01', '2022-12-03', 1, 1, 1, 'hadi', '2023-01-01 14:51:17.000000', 34),
(2, '2022-12-01', '2022-12-29', 1, 1, 1, 'hadi', '2022-12-29 20:26:36.000000', 34),
(4, '2023-01-04', '2023-01-11', 0, 0, 0, 'kevin', '2023-01-04 20:08:02.000000', 37);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `username` varchar(50) NOT NULL,
  `password` varchar(260) NOT NULL,
  `useremail` varchar(100) NOT NULL,
  `formofadress` varchar(30) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `secondname` varchar(50) NOT NULL,
  `path` varchar(260) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`id`, `admin`, `active`, `username`, `password`, `useremail`, `formofadress`, `firstname`, `secondname`, `path`) VALUES
(23, 1, 1, 'admin', '$2y$10$Pgf7Rd4eqE1l3A8sdQAE2.USLzQFsj36/4GuBakz6dALR1WYxKPvO', 'wi21b030@technikum-wien.at', '1', 'Safwan', 'Zullash', 'uploads/profilepics/admin.jpg'),
(34, 0, 1, 'hadi', '$2y$10$gdYK9rXxm67eJC261gH.5.j.4Q7V2SzWeCK6vl.vcpE7.pPkegR3S', 'hadi@gmail.com', '1', 'Hadi', 'Heydari', 'uploads/profilepics/hadi.jpg'),
(37, 0, 1, 'kevin', '$2y$10$z1HLqBvoGXqOzOBD5W2amOD9gJW.HdI2WCAvJbuNBQGUpzhQWMRLm', 'kevin@xhunga.at', '1', 'Kevin', 'Xhunga', 'uploads/profilepics/kevin.jpg');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `title` (`title`);

--
-- Indizes für die Tabelle `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT für Tabelle `reservation`
--
ALTER TABLE `reservation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
