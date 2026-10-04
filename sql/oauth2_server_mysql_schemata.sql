-- =========================================================
-- OAuth2 Authorization Server - MySQL Schema
-- =========================================================

CREATE DATABASE IF NOT EXISTS hahela_oauth2_server
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hahela_oauth2_server;

-- Registrierte Clients
CREATE TABLE oauth_clients (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id     VARCHAR(64)  NOT NULL UNIQUE,
  client_secret VARCHAR(128) NULL,           -- NULL für öffentliche Clients (PKCE)
  client_name   VARCHAR(191) NOT NULL,
  redirect_uri  VARCHAR(500) NOT NULL,
  is_confidential TINYINT(1) NOT NULL DEFAULT 1,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Resource Owner (Benutzer)
CREATE TABLE oauth_users (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username   VARCHAR(191) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,        -- password_hash(PASSWORD_DEFAULT)
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Authorization Codes (kurzlebig, einmalig)
CREATE TABLE oauth_auth_codes (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code        VARCHAR(128) NOT NULL UNIQUE,
  client_id   VARCHAR(64)  NOT NULL,
  user_id     INT UNSIGNED NULL,
  redirect_uri VARCHAR(500) NOT NULL,
  scope       VARCHAR(500) NOT NULL DEFAULT '',
  code_challenge VARCHAR(191) NULL,
  code_challenge_method VARCHAR(10) NULL,     -- S256 | plain
  expires_at  DATETIME NOT NULL,
  used        TINYINT(1) NOT NULL DEFAULT 0,
  INDEX idx_code_expiry (code, expires_at)
) ENGINE=InnoDB;

-- Access Tokens
CREATE TABLE oauth_access_tokens (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  token      VARCHAR(128) NOT NULL UNIQUE,    -- SHA-256-Hash des Tokens
  client_id  VARCHAR(64) NOT NULL,
  user_id    INT UNSIGNED NULL,
  scope      VARCHAR(500) NOT NULL DEFAULT '',
  revoked    TINYINT(1) NOT NULL DEFAULT 0,
  expires_at DATETIME NOT NULL,
  INDEX idx_token_lookup (token, expires_at)
) ENGINE=InnoDB;

-- Refresh Tokens
CREATE TABLE oauth_refresh_tokens (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  token      VARCHAR(128) NOT NULL UNIQUE,    -- SHA-256-Hash des Tokens
  client_id  VARCHAR(64) NOT NULL,
  user_id    INT UNSIGNED NULL,
  scope      VARCHAR(500) NOT NULL DEFAULT '',
  revoked    TINYINT(1) NOT NULL DEFAULT 0,
  expires_at DATETIME NOT NULL
) ENGINE=InnoDB;

-- Beispieldaten
-- Öffentlicher Testclient mit PKCE:
INSERT INTO oauth_clients (client_id, client_secret, client_name, redirect_uri, is_confidential)
VALUES ('demo-app', NULL, 'Demo App', 'http://localhost:3000/callback', 0);

-- Konfentieller Client (Secret hier als SHA2-Hash gespeichert):
INSERT INTO oauth_clients (client_id, client_secret, client_name, redirect_uri, is_confidential)
VALUES ('backend-service', SHA2('streng-geheim', 256), 'Backend Service', '', 1);