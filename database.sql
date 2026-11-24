-- Happy Gym PRO — MySQL / MariaDB schema
-- Matches the queries used by PHP_8_Web_Backend_Application.
-- Import with: mysql -u root -p < database.sql

CREATE DATABASE IF NOT EXISTS gym_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE gym_db;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(32) NOT NULL,
  password VARCHAR(255) NOT NULL,
  weight DECIMAL(5,2) DEFAULT NULL,
  height DECIMAL(5,2) DEFAULT NULL,
  age TINYINT UNSIGNED DEFAULT NULL,
  gender VARCHAR(20) DEFAULT NULL,
  activity VARCHAR(40) DEFAULT NULL,
  fitness_goal VARCHAR(60) DEFAULT NULL,
  avatar VARCHAR(255) DEFAULT NULL,
  reset_otp CHAR(6) DEFAULT NULL,
  reset_otp_expiry DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  UNIQUE KEY uq_users_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS logged_meals (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  meal_name VARCHAR(150) NOT NULL,
  calories INT DEFAULT 0,
  meal_content MEDIUMTEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_logged_meals_user (user_id),
  CONSTRAINT fk_logged_meals_user
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
