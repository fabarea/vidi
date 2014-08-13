#
# Table structure for table 'tx_vidi_domain_model_selection'
#
CREATE TABLE tx_vidi_domain_model_selection (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

	type int(11) DEFAULT '0' NOT NULL,
	name varchar(255) DEFAULT '' NOT NULL,
	data_type varchar(255) DEFAULT '' NOT NULL,
	matches text,

	PRIMARY KEY (uid),
	KEY parent (pid),
);