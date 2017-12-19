-- User table

CREATE TABLE IF NOT EXISTS zxc.users
(
  id                    SERIAL      NOT NULL,
  login                 VARCHAR(20) NOT NULL,
  password              VARCHAR(40) NOT NULL,
  email                 VARCHAR(30) NOT NULL,
  accountactivationattr INTEGER     NOT NULL,
  firstname             VARCHAR(15),
  lastname              VARCHAR(15),
  accountactivationkey  VARCHAR(40),
  session               VARCHAR(40),
  reminderkey           VARCHAR(40),
  avatar                VARCHAR(30),
  fonimage              VARCHAR(30),
  basedirectory         VARCHAR(15),
  role                  INTEGER,
  CONSTRAINT users_login_id_pk
  PRIMARY KEY (login, id)
);

CREATE UNIQUE INDEX IF NOT EXISTS users_id_uindex
  ON zxc.users (id);

CREATE UNIQUE INDEX IF NOT EXISTS users_login_uindex
  ON zxc.users (login);

CREATE UNIQUE INDEX IF NOT EXISTS users_email_uindex
  ON zxc.users (email);
