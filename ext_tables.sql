#
# Schema for additional fields for fe_users table
#
CREATE TABLE fe_users (
    date_of_birth varchar(50) DEFAULT '' NOT NULL,
    place_of_birth varchar(50) DEFAULT '' NOT NULL
);
