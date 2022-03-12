#
# Schema for additional fields for fe_users table
#
CREATE TABLE fe_users (
    date_of_birth varchar(50) DEFAULT '' NOT NULL,
    place_of_birth varchar(50) DEFAULT '' NOT NULL
);

#
# Schema for saving double opt in information
#
CREATE TABLE form_double_opt_in
(
    uid int(11) unsigned DEFAULT 0 NOT NULL auto_increment,
    pid int(11) DEFAULT 0 NOT NULL,
    email varchar(255) NOT NULL,
    firstname varchar (255) DEFAULT '',
    lastname varchar(255) DEFAULT '',
    hash varchar(255),
    verified int(1) DEFAULT 0,

    tstamp int(11) unsigned DEFAULT 0 NOT NULL,
    crdate int(11) unsigned DEFAULT 0 NOT NULL,
    deleted tinyint(4) unsigned DEFAULT 0 NOT NULL,
    hidden tinyint(4) unsigned DEFAULT 0 NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
);