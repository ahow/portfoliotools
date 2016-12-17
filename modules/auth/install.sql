CREATE TABLE mc_groups
( id integer NOT NULL AUTO_INCREMENT,
  grname varchar(120) NOT NULL,
  PRIMARY KEY (id )
) ENGINE=InnoDB  CHARSET=utf8;


CREATE TABLE mc_users (
  id integer NOT NULL AUTO_INCREMENT,
  created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  name varchar(220) NOT NULL,
  lastname varchar(220) NOT NULL,
  firstname varchar(220) NOT NULL,
  email varchar(220) DEFAULT NULL,
  phone varchar(16) DEFAULT NULL,
  pass varchar(64) DEFAULT NULL,
  image varchar(512) DEFAULT NULL,
  auth_id varchar(64) DEFAULT NULL,
  auth_module varchar(60) DEFAULT NULL,
  chpass_key varchar(100) DEFAULT NULL,
  email_checked smallint(6) DEFAULT '0',
  phone_checked smallint(6) DEFAULT '0',
  PRIMARY KEY (id),
  KEY auth_module (auth_module),
  KEY auth_id (auth_id),
  KEY name (name)
) ENGINE=InnoDB  CHARSET=utf8


CREATE TABLE mc_usergroups
( user_id integer NOT NULL AUTO_INCREMENT,
  group_id integer NOT NULL,
  PRIMARY KEY (user_id , group_id ),
  FOREIGN KEY (user_id) references mc_users(id) on update cascade,
  FOREIGN KEY (group_id) references mc_groups(id) on update cascade
) ENGINE=InnoDB  CHARSET=utf8;

CREATE TABLE mc_sessions
( id integer NOT NULL AUTO_INCREMENT,
  user_id integer NOT NULL,
  session varchar(50) NOT NULL,
  ttl bigint,
  PRIMARY KEY (id ),
  FOREIGN KEY (user_id) references mc_users(id) on delete cascade on update cascade
) ENGINE=InnoDB  CHARSET=utf8;

insert into mc_groups values (1,'admin'),(2,'user'),(3,'tutor'),(4,'student');
insert into mc_users values (1,'admin','','Администратор','','','21232f297a57a5a743894a0e4a801fc3');
insert into mc_usergroups values (1,1);

