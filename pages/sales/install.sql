-- SMega
CREATE TABLE sales_industry_groups -- Super table
( id integer NOT NULL AUTO_INCREMENT,
  industry_group varchar(220) not null,
  division  varchar(220),
  major_group  varchar(220),
  primary key (id)
) DEFAULT CHARSET=utf8;

CREATE TABLE sales_sic
( id integer NOT NULL,
  name varchar(220) NOT NULL,
  description text,
  industry_group_id integer NOT NULL,
  primary key (id),
  foreign key (industry_group_id) references sales_industry_groups(id)
) DEFAULT CHARSET=utf8;

CREATE TABLE sales_companies
( cid varchar(16) NOT NULL,
  name varchar(220) NOT NULL,
  modified timestamp null,
  modified_by integer,
  industry_group varchar(220),
  industry  varchar(220),
  sector  varchar(100),
  subsector  varchar(100),
  country  varchar(100),
  isin varchar(32),
  region varchar(64),
  index(region),
  index(name),
  index(industry),
  index(isin),
  primary key (cid)
) DEFAULT CHARSET=utf8;


CREATE TABLE sales_divdetails
( cid varchar(16) NOT NULL,
  division smallint  not null,
  syear smallint not null,
  modified timestamp null,
  modified_by integer,
  me varchar(220) not null,
  sic integer not null,
  sales double precision not null,
  primary key (cid,division,syear),
  foreign key (sic) references sales_sic(id)
);



