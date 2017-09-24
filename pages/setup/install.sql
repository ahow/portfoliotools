CREATE TABLE mc_groups
( id integer NOT NULL AUTO_INCREMENT,
  grname varchar(120) NOT NULL,
  PRIMARY KEY (id )
) DEFAULT CHARSET=utf8 ;

CREATE TABLE mc_users
( id integer NOT NULL AUTO_INCREMENT,
  created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  name varchar(220) NOT NULL,
  lastname varchar(220) NOT NULL,
  firstname varchar(220) NOT NULL,
  email varchar(220),
  phone varchar(16),
  pass varchar(64),
  image varchar(512),
  auth_id varchar(64),
  auth_module varchar(60),
  chpass_key varchar(100),FOREIGN KEY (user_id) references mc_users(id) on delete cascade on update cascade,
  email_checked smallint default 0,
  phone_checked smallint default 0,
  INDEX (auth_module),
  INDEX (auth_id),
  INDEX (name),
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8;

CREATE TABLE mc_usergroups
( user_id integer NOT NULL AUTO_INCREMENT,
  group_id integer NOT NULL,
  PRIMARY KEY (user_id , group_id ),
  FOREIGN KEY (user_id) references mc_users(id) on delete cascade on update cascade,
  FOREIGN KEY (group_id) references mc_groups(id) on update cascade
) DEFAULT CHARSET=utf8;

CREATE TABLE mc_sessions
( id integer NOT NULL AUTO_INCREMENT,
  user_id integer NOT NULL,
  session varchar(50) NOT NULL,
  ttl bigint,
  PRIMARY KEY (id ),
  FOREIGN KEY (user_id) references mc_users(id) on delete cascade on update cascade
) DEFAULT CHARSET=utf8;

insert into mc_groups values (1,'admin'),(2,'user'),(3,'editor');
insert into mc_users (id,name,firstname,lastname,pass) values (1,'admin','Administrator','','$2y$10$A.LAT98F0.RROhxuWTB3c.JjejxA.tv4faNetfRCJPf0HZXCp1j0m');
insert into mc_usergroups values (1,1);

CREATE TABLE mc_pages
( name varchar(120) NOT NULL,
  update_no integer default 0,
  PRIMARY KEY (name)
) DEFAULT CHARSET=utf8;


CREATE TABLE settings
(  name varchar(128) not null,
   json text  not null,
   PRIMARY KEY (name)
) DEFAULT CHARSET=utf8;

insert into settings values ('email','{}');

COMMIT;
