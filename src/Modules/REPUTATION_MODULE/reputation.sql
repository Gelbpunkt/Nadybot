CREATE TABLE IF NOT EXISTS reputation (
	`id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`name` TEXT NOT NULL,
	`reputation` TEXT NOT NULL,
	`comment` TEXT NOT NULL,
	`by` TEXT NOT NULL,
	`dt` INT NOT NULL
);
