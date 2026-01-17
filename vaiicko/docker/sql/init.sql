DROP EVENT IF EXISTS `expire_reservations`;

DROP TABLE IF EXISTS `reservation`;
DROP TABLE IF EXISTS `book_copy`;
DROP TABLE IF EXISTS `books`;
DROP TABLE IF EXISTS `authors`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `genres`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `authors`
(
    `id`          int(11) NOT NULL AUTO_INCREMENT,
    `first_name`  varchar(50) NOT NULL,
    `last_name`   varchar(50) NOT NULL,
    `birth_year`  SMALLINT(4) DEFAULT NULL,
    `death_year`  SMALLINT(4) DEFAULT NULL,
    `photo`       varchar(255) DEFAULT NULL,
    `description` text         DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Create categories
CREATE TABLE `categories`
(
    `id`          int(11) NOT NULL AUTO_INCREMENT,
    `name`        varchar(100) NOT NULL,
    `description` text DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Create genres
CREATE TABLE `genres`
(
    `id`          int(11) NOT NULL AUTO_INCREMENT,
    `name`        varchar(100) NOT NULL,
    `description` text DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Create books (depends on authors, categories, genres)
CREATE TABLE `books`
(
    `id`             int(11) NOT NULL AUTO_INCREMENT,
    `isbn`           varchar(50)  NOT NULL,
    `year_published` SMALLINT(4)  NOT NULL,
    `publisher`      varchar(255)  NOT NULL,
    `description`    varchar(100) DEFAULT NULL,
    `title`          varchar(100) NOT NULL,
    `author_id`      int(11) DEFAULT NULL,
    `category_id`    int(11) DEFAULT NULL,
    `genre_id`       int(11) DEFAULT NULL,
    `photo`          varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY              `author_id` (`author_id`),
    KEY              `category_id` (`category_id`),
    KEY              `genre_id` (`genre_id`),
    CONSTRAINT `books_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `books_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `books_ibfk_3` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Create book_copy (depends on books)
CREATE TABLE `book_copy`
(
    `id`        int(11) NOT NULL AUTO_INCREMENT,
    `available` tinyint(1) DEFAULT 1,
    `book_id`   int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY         `book_id` (`book_id`),
    CONSTRAINT `book_copy_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Create users (independent)
CREATE TABLE `users`
(
    `id`         int(11) NOT NULL AUTO_INCREMENT,
    `username`   varchar(100) DEFAULT NULL,
    `password`   varchar(255) DEFAULT NULL,
    `email`      varchar(255) DEFAULT NULL,
    `role`       varchar(50)  DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Create reservation (depends on book_copy and users)
CREATE TABLE `reservation`
(
    `id`           int(11) NOT NULL AUTO_INCREMENT,
    `is_reserved`  varchar(50) DEFAULT NULL,
    `user_id`      int(11) DEFAULT NULL,
    `book_copy_id` int(11) DEFAULT NULL,
    `created_at`   timestamp NULL DEFAULT current_timestamp(),
    reserved_until timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY            `book_copy_id` (`book_copy_id`),
    KEY            `user_id` (`user_id`),
    CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`book_copy_id`) REFERENCES `book_copy` (`id`) ON DELETE SET NULL,
    CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

DROP EVENT IF EXISTS `expire_reservations`;
CREATE EVENT `expire_reservations`
    ON SCHEDULE EVERY 1 HOUR
    STARTS '2026-01-03 10:32:20'
    ON COMPLETION PRESERVE
    ENABLE
DO
    UPDATE reservation
    SET is_reserved = 0
    WHERE is_reserved = 1
      AND reserved_until <= NOW();
