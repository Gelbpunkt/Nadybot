CREATE TABLE IF NOT EXISTS banlist_<myname> (
	charid BIGINT NOT NULL PRIMARY KEY,
	admin VARCHAR(25),
	time INT,
	reason TEXT,
	banend INT
);