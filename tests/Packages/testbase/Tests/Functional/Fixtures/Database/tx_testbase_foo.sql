CREATE TABLE tx_testbase_foo (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	testdate datetime DEFAULT '0000-00-00 00:00:00:00',

	PRIMARY KEY (uid),
	KEY parent (pid)
);
