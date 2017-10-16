alter table sales_divdetails modify sales double;

-- SALES;MARKET CAP;Sales growth;ROIC;PE;EVEBITDA;Payout
ALTER TABLE sales_companies add sales double;
ALTER TABLE sales_companies add market_cap double;
ALTER TABLE sales_companies add sales_growth double;
ALTER TABLE sales_companies add roic double;
ALTER TABLE sales_companies add pe double;
ALTER TABLE sales_companies add evebitda double;
ALTER TABLE sales_companies add payout double;
ALTER TABLE sales_companies add reviewed boolean default false;



-- Climate change	Demographics	Regulation	Another theme
ALTER TABLE sales_sic add exposure text;

create table sales_exposure
(  id integer not null,
   headers text not null,
   primary key (id)
);

insert into sales_exposure values (1,'');


CREATE TABLE sales_metrics
( id integer NOT NULL AUTO_INCREMENT,
  metric varchar(220) not null,
  description  varchar(400),
  created timestamp null default current_timestamp,
  primary key (id)
) DEFAULT CHARSET=utf8;

CREATE TABLE sales_metrics_data
( metric_id integer NOT NULL,
  isin varchar(32) NOT NULL,
  col  smallint NOT NULL default 1, 
  val double NOT NULL,
  primary key (metric_id, isin, col),
  index(isin),
  foreign key (metric_id) references sales_metrics(id) on delete CASCADE
) DEFAULT CHARSET=utf8;

CREATE TABLE sales_metrics_columns
( col  smallint NOT NULL default 1, 
  metric_id integer NOT NULL,
  name varchar(220) NOT NULL,
  primary key (metric_id, col),
  foreign key (metric_id) references sales_metrics(id) on delete CASCADE
) DEFAULT CHARSET=utf8;


CREATE TABLE sales_portfolio
( id integer NOT NULL AUTO_INCREMENT,
  portfolio varchar(220) not null,
  description  varchar(400),
  created timestamp null default current_timestamp,
  primary key (id)
) DEFAULT CHARSET=utf8;

CREATE TABLE sales_portfolio_data
( portfolio_id integer NOT NULL,
  isin varchar(32) NOT NULL,
  val double NOT NULL,
  primary key (portfolio_id, isin),
  index(isin),
  foreign key (portfolio_id) references sales_portfolio(id) on delete CASCADE
) DEFAULT CHARSET=utf8;

--CREATE FUNCTION SPLIT_STRING(str VARCHAR(255), delim VARCHAR(12), pos INT)
--RETURNS VARCHAR(255)
--RETURN REPLACE(SUBSTRING(SUBSTRING_INDEX(str, delim, pos),
--       LENGTH(SUBSTRING_INDEX(str, delim, pos-1)) + 1),
--       delim, '');


-- extract doubl value from CSV string
delimiter $$

CREATE FUNCTION CSV_DOUBLE(str VARCHAR(255), pos INT)
RETURNS DOUBLE
RETURN 1.0*REPLACE(SUBSTRING(SUBSTRING_INDEX(str, ';', pos),
       LENGTH(SUBSTRING_INDEX(str, ';', pos-1)) + 1), ';', '')$$
DELIMITER ;    

-- set default password to admin       
update mc_users set pass='$2y$10$EdileunVjDRYQxPBDCjM2exlKLsI8cX7m5i5V63nAs.WKCMrjOuzu' where name='admin';

CREATE TABLE sales_isin_matching
( isin varchar(32) NOT NULL,
  isin_alias varchar(32) NOT NULL,
  index(isin_alias),
  primary key (isin, isin_alias)
) DEFAULT CHARSET=utf8;


CREATE TABLE sales_portfolio_summaries
( id integer NOT NULL AUTO_INCREMENT,
  portfolio_id integer not null,
  description  varchar(400),
  created timestamp null default current_timestamp,
  json text,
  primary key (id),
  foreign key (portfolio_id) references sales_portfolio(id) on delete CASCADE
) DEFAULT CHARSET=utf8;

alter table sales_divdetails add ebit double;
alter table sales_divdetails add assets double;
alter table sales_divdetails add capex double;

insert into sales_industry_groups 
(id, industry_group, division, major_group) values
(-1, 'Unclassified','Unclassified','Unclassified');

insert into sales_sic (id, name, description, industry_group_id)
values (-1, 'Unclassified','Unclassified', -1);




