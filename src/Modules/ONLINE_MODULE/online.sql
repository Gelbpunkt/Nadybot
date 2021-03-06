CREATE TABLE IF NOT EXISTS `online` (
	`name` CHAR(25) NOT NULL,
	`afk` VARCHAR(255) DEFAULT '',
	`channel` CHAR(50),
	`channel_type` CHAR(10) NOT NULL,
	`added_by` CHAR(25) NOT NULL,
	`dt` INT NOT NULL,
	UNIQUE(name, channel_type, added_by)
);
DELETE FROM `online` WHERE `added_by` = '<myname>';