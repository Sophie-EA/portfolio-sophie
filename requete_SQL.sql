#CREATE DATABASE Portfolio;

CREATE TABLE `projects` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`slug` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`title` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`short_description` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`description` TEXT NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`technologies` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`image` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`github_url` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`demo_url` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`project_date` DATE NULL DEFAULT NULL,
	`has_custom_assets` TINYINT(1) NULL DEFAULT '0',
	PRIMARY KEY (`id`) USING BTREE,
	UNIQUE INDEX `slug` (`slug`) USING BTREE,
	UNIQUE INDEX `slug_2` (`slug`) USING BTREE,
	UNIQUE INDEX `slug_3` (`slug`) USING BTREE
)
COLLATE='utf8mb4_uca1400_ai_ci'
ENGINE=InnoDB
AUTO_INCREMENT=20
;


CREATE TABLE `project_images` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`project_id` INT(11) NOT NULL,
	`image_path` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`alt_text` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`display_order` INT(11) NULL DEFAULT '0',
	`created_at` TIMESTAMP NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `project_id` (`project_id`) USING BTREE,
	CONSTRAINT `fk_project_images` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON UPDATE RESTRICT ON DELETE CASCADE
)
COLLATE='utf8mb4_uca1400_ai_ci'
ENGINE=InnoDB
AUTO_INCREMENT=5
;

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE `contacts` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`email` VARCHAR(150) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`subject` VARCHAR(150) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`message` TEXT NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`created_at` TIMESTAMP NULL DEFAULT current_timestamp(),
	`is_read` TINYINT(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`) USING BTREE
)
COLLATE='utf8mb4_uca1400_ai_ci'
ENGINE=InnoDB
AUTO_INCREMENT=9
;

