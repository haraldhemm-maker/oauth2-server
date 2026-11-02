-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 30. Okt 2026 um 10:15
-- Server-Version: 10.11.14-MariaDB-0ubuntu0.24.04.1
-- PHP-Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `hahela_oauth2_server`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `oauth_access_tokens`
--

CREATE TABLE `oauth_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `token` varchar(128) NOT NULL,
  `client_id` varchar(64) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `scope` varchar(500) NOT NULL DEFAULT '',
  `revoked` tinyint(1) NOT NULL DEFAULT 0,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `oauth_auth_codes`
--

CREATE TABLE `oauth_auth_codes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(128) NOT NULL,
  `client_id` varchar(64) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `redirect_uri` varchar(500) NOT NULL,
  `scope` varchar(500) NOT NULL DEFAULT '',
  `code_challenge` varchar(191) DEFAULT NULL,
  `code_challenge_method` varchar(10) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `oauth_clients`
--

CREATE TABLE `oauth_clients` (
  `id` int(10) UNSIGNED NOT NULL,
  `client_id` varchar(64) NOT NULL,
  `client_secret` varchar(128) DEFAULT NULL,
  `client_name` varchar(191) NOT NULL,
  `redirect_uri` varchar(500) NOT NULL,
  `is_confidential` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `allowed_scopes` varchar(255) NOT NULL DEFAULT 'profile'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `oauth_clients`
--

INSERT INTO `oauth_clients` (`id`, `client_id`, `client_secret`, `client_name`, `redirect_uri`, `is_confidential`, `created_at`, `allowed_scopes`) VALUES
(1, 'web-app', NULL, 'Web-App', 'http://localhost/~harald/app/web-app/php/callback.php', 0, '2026-10-26 13:48:49', 'profile');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `oauth_refresh_tokens`
--

CREATE TABLE `oauth_refresh_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `token` varchar(128) NOT NULL,
  `client_id` varchar(64) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `scope` varchar(500) NOT NULL DEFAULT '',
  `revoked` tinyint(1) NOT NULL DEFAULT 0,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `oauth_refresh_tokens`
--

INSERT INTO `oauth_refresh_tokens` (`id`, `token`, `client_id`, `user_id`, `scope`, `revoked`, `expires_at`) VALUES
(3, 'd9805ee5403de33e95ddaa90b742a3bd634889fe106db87ccd0fc8a3a4bd9f7a', 'web-app', 1, '', 0, '2026-11-08 22:03:54'),
(4, '37e8018cc37ed24dbfaf8adbaf21c8cc5aed18e0a5d8a513d6eb45b5dac8566a', 'web-app', 1, '', 0, '2026-11-10 19:03:22'),
(5, '04d41209c338a5befeecaea1be3f2669e27a60f76e8999a92982009aaf180bb8', 'web-app', 1, '', 0, '2026-11-10 19:12:10'),
(6, 'df2c80ca1d1ced8b8bd87eddcafb8a95d0fef391029d7e9ec589c9ee6135a879', 'web-app', 1, '', 0, '2026-11-10 19:15:56'),
(7, 'fa5405271a7c6deb917237ef1309bab784ad2af14108b41df06078d83baf4b88', 'web-app', 1, '', 0, '2026-11-10 19:25:21'),
(8, '60b9057ae2a6894973727a9780ac70490049e927433d8db7476f0c18347a633c', 'web-app', 1, '', 0, '2026-11-11 13:52:39'),
(9, 'b830224643e5bb86ef5ec3fe6d6fc4c6ec422381d60acfcbd6aef09855646f3c', 'web-app', 1, '', 0, '2026-11-11 13:52:43'),
(10, '3189dbd28146b3bcf8a835ce02105f6521108eb04caba99612d1308215d6e5cc', 'web-app', 1, '', 0, '2026-11-11 13:55:37'),
(11, '7ea2e17feeeb9ba89965653fc7db93b5d90ab71fdeae7feb0cc87fc7f4b6deea', 'web-app', 1, '', 0, '2026-11-11 14:02:04'),
(12, '637b36280071468a9c065bde3ce26bcd4b16067dbce45fce4583b5cbe8abf767', 'web-app', 1, '', 0, '2026-11-11 14:27:26'),
(13, '3531ee266dc74d4352c368640e631c03fbe9e52e5e971e4a744a67053821b0db', 'web-app', 1, '', 0, '2026-11-11 14:40:34'),
(14, '2bd4e225d661cfbfcd1ec5f8394548d5ddddb6a66a315ee7b577cfb94041b4cc', 'web-app', 1, '', 0, '2026-11-11 22:10:58'),
(15, 'b0bf83107d1e00b141ad41384d633e2a7d7bce358a58aaf584742c3a873dfa3b', 'web-app', 1, '', 0, '2026-11-11 22:11:20'),
(16, 'e9c6f47fd6752aa5c050e6fa2b663a4074fe61d55b23e97f662ef3b9037012f7', 'web-app', 1, '', 0, '2026-11-11 23:02:31'),
(17, '5c236f8234ebf57f03d31974c404eb98eb7356ce77ec30df6ccde483d5bd0b11', 'web-app', 1, '', 0, '2026-11-26 13:49:27'),
(18, '36642f4a5030c849d5222f31a1ba19f8aad4c694902af245e289bb3c0f6b5b1f', 'web-app', 1, '', 0, '2026-11-26 14:23:50'),
(19, '91edda66797c6e4c4c54e9bbce50afd58edbeafeac8f82b04fc189aced641e40', 'web-app', 1, '', 0, '2026-11-27 20:10:39'),
(20, 'eb5871537fa789b329c9979c25447383ab5605ebb6fc257d1fd0de0eb340b6d5', 'web-app', 1, '', 0, '2026-11-27 20:17:00');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `oauth_users`
--

CREATE TABLE `oauth_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(191) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `role` enum('guest','user','admin') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `oauth_users`
--

INSERT INTO `oauth_users` (`id`, `username`, `password_hash`, `created_at`, `role`) VALUES
(1, 'harald', '$2y$10$ScjHNRytbqoFpxnP8zxOjONeiUo5e0tj3Aou4QkHO0xt0zBwSggTe', '2026-10-26 15:55:34', 'user');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token_lookup` (`token`,`expires_at`);

--
-- Indizes für die Tabelle `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code_expiry` (`code`,`expires_at`);

--
-- Indizes für die Tabelle `oauth_clients`
--
ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `client_id` (`client_id`);

--
-- Indizes für die Tabelle `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indizes für die Tabelle `oauth_users`
--
ALTER TABLE `oauth_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT für Tabelle `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT für Tabelle `oauth_clients`
--
ALTER TABLE `oauth_clients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT für Tabelle `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT für Tabelle `oauth_users`
--
ALTER TABLE `oauth_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
