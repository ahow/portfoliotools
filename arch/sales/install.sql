CREATE TABLE sales_industry_groups
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


/*
CREATE TABLE sales_uploads
( id integer NOT NULL AUTO_INCREMENT,
  created timestamp not null default current_timestamp,
  filename varchar(250) not null,
  success boolean not null default FALSE,
  primary key (id)
);

CREATE TABLE sales_div_sales
( id integer NOT NULL AUTO_INCREMENT,
  cid varchar(16) NOT NULL,
  sales double precision not null, 
  upload_id integer not null,
  foreign key (upload_id) references sales_uploads(id) on delete cascade,
  primary key (id)
);
*/

-- div_sale_id integer not null,

CREATE TABLE sales_divdetails
( id integer NOT NULL AUTO_INCREMENT,
  is_modified bool not null default false,
  cid varchar(16) NOT NULL,
  division smallint  not null,
  syear smallint not null,
  me varchar(220) not null,
  sic integer not null,
  sales double precision not null,
  foreign key (div_sale_id) references sales_div_sales(id) on delete cascade,
  primary key (id)
);

