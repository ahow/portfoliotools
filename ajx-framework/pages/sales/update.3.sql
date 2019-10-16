alter table sales_companies add EBITDA_growth double;
alter table sales_companies add ROE double;
alter table sales_companies add yield double;
alter table sales_companies add price_to_book double;
alter table sales_companies add reinvestment double;
alter table sales_companies add research_and_development double;
alter table sales_companies add net_debt_to_EBITDA double;
alter table sales_companies add CAPE double;
alter table sales_companies add sustain_ex double;

CREATE TABLE sales_theams
( id integer NOT NULL auto_increment,
  theam varchar(255) NOT NULL,
  unique(theam),
  primary key (id)
) DEFAULT CHARSET=utf8;

CREATE TABLE sales_company_theams
( cid varchar(16) not null,
  theam_id integer not null,
  theam_value double precision not null,
  primary key (cid, theam_id),
  foreign key (theam_id) references sales_theams(id)
     on delete cascade on update cascade
) DEFAULT CHARSET=utf8;

CREATE TABLE sales_sic_theams
( sic_id integer not null,
  theam_id integer not null,
  theam_value double precision not null,
  primary key (sic_id),
  foreign key (theam_id) references sales_theams(id)
     on delete cascade on update cascade,
  foreign key (sic_id) references sales_sic(id)
     on delete cascade on update cascade
) DEFAULT CHARSET=utf8;
