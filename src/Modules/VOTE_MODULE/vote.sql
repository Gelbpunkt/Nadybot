CREATE TABLE IF NOT EXISTS vote_<myname> (
	`question` TEXT(500),
	`author` TEXT (80),
	`started` INT (10),
	`duration` INT (10),
	`answer` TEXT(500),
	`status` INT (1)
);