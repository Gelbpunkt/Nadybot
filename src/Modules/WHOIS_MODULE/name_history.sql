CREATE TABLE IF NOT EXISTS name_history (
	`charid` BIGINT NOT NULL,
	`name` VARCHAR(20) NOT NULL,
	`dimension` INT NOT NULL,
	`dt` INT NOT NULL,
	PRIMARY KEY (charid, name, dimension)
);