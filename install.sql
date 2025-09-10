-- SQL to create tables for WP Flight Aggregator
CREATE TABLE IF NOT EXISTS `wp_flight_bookings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `flight_id` VARCHAR(100) NOT NULL,
  `source` VARCHAR(20) NOT NULL,
  `route` VARCHAR(100) NOT NULL,
  `departure_time` DATETIME NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `passenger_name` VARCHAR(200) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `mobile` VARCHAR(30) NOT NULL,
  `seat_count` INT UNSIGNED NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY (`email`),
  KEY (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `wp_booking_conflicts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` BIGINT UNSIGNED NOT NULL,
  `conflict_type` VARCHAR(50) NOT NULL,
  `data` LONGTEXT NOT NULL,
  `is_resolved` TINYINT(1) NOT NULL DEFAULT 0,
  `resolved_by` BIGINT UNSIGNED NULL,
  `resolution_note` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY (`booking_id`),
  KEY (`is_resolved`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
