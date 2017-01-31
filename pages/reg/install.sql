CREATE TABLE evnamegl
( id integer NOT NULL AUTO_INCREMENT,
  created timestamp not null default current_timestamp,
  firstname varchar(128) not null,
  lastname varchar(128) not null,
  email varchar(255) not null,
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8;

CREATE TABLE evnamegl_guests
( id integer NOT NULL AUTO_INCREMENT,
  engl_id integer NOT NULL,
  firstname varchar(128) not null,
  lastname varchar(128) not null,
  PRIMARY KEY (id),
  FOREIGN KEY (engl_id) REFERENCES evnamegl(id) on update cascade on delete cascade
) DEFAULT CHARSET=utf8;
