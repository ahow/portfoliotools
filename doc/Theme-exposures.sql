-- getExposuresByPortfolio year=2015 portfolio=3

set @year=2015;
set @pf=4; -- 1a    5 is 2a

-- choose 
CREATE TEMPORARY TABLE tmp_cids (cid varchar(16) NOT NULL, isin varchar(32),
 primary key (cid), index(isin))  ENGINE=MEMORY;

-- saving not reviewed rows
insert into tmp_cids
select 
    c.cid, c.isin
from sales_companies c
join sales_divdetails d on c.cid = d.cid and d.syear=@year
join sales_portfolio_data p on c.isin = p.isin and p.portfolio_id=@pf
where not c.reviewed
group by 1,2;


CREATE TEMPORARY TABLE subsector_nrw_total (subsector  varchar(100), total double, index(subsector)) ENGINE=MEMORY;

-- стало select sum(total),count(*) from subsector_nrw_total;
insert into subsector_nrw_total
select 
 c.subsector, sum(d.sales)
from tmp_cids t
join sales_companies c on t.cid=c.cid
join sales_divdetails d on c.cid = d.cid
where d.syear=@year and not c.reviewed
group by 1;


CREATE TEMPORARY TABLE subsector_nrw_values (subsector  varchar(100), p1 double, p2 double, p3 double, p4 double, index(subsector)) ENGINE=MEMORY;

insert into subsector_nrw_values
select 
 c.subsector, sum(d.sales/t.total*CSV_DOUBLE(s.exposure,1)), sum(d.sales/t.total*CSV_DOUBLE(s.exposure,2)) 
 , sum(d.sales/t.total*CSV_DOUBLE(s.exposure,3)) , sum(d.sales/t.total*CSV_DOUBLE(s.exposure,4)) 
from tmp_cids tc
join sales_companies c on tc.cid=c.cid
join sales_divdetails d on c.cid = d.cid
join sales_sic s on d.sic=s.id
join subsector_nrw_total t on c.subsector=t.subsector
where d.syear=@year and not c.reviewed 
group by 1;





/*
select count(*) as count , 
 sum(e.p1*p.val)/@divider as s1,
 sum(e.p2*p.val)/@divider as s2, 
 sum(e.p3*p.val)/@divider as s3, 
 sum(e.p4*p.val)/@divider as s4 
from sales_companies c
join sales_portfolio_data p on c.isin = p.isin and portfolio_id=@pf
join subsector_nrw_values e on c.subsector = e.subsector
where not c.reviewed 
and c.cid in (select distinct cid from sales_divdetails where syear=@year)
*/

CREATE TEMPORARY TABLE tmp_cid_values (cid varchar(16) NOT NULL,
isin varchar(32), reviewed boolean, p1 double, p2 double, p3 double, p4 double) ENGINE=MEMORY;

-- saving calculated values
insert into tmp_cid_values
select
 c.cid,
 c.isin,
 false,
 e.p1*p.val as s1 ,
 e.p2*p.val as s2 ,
 e.p3*p.val as s3 ,
 e.p4*p.val as s4
from tmp_cids t
join sales_companies c on t.cid=c.cid
join sales_portfolio_data p on c.isin = p.isin and portfolio_id=@pf
join subsector_nrw_values e on c.subsector = e.subsector;

-- create table for store total sales
CREATE TEMPORARY TABLE tmp_rv_total (cid varchar(16) NOT NULL, isin varchar(32), total_sales double,
 primary key (cid), index(isin))  ENGINE=MEMORY;

--  reviewed rows
insert into tmp_rv_total
select 
    c.cid, c.isin, sum(d.sales)
from sales_companies c
join sales_divdetails d on c.cid = d.cid and d.syear=@year
join sales_portfolio_data p on c.isin = p.isin and p.portfolio_id=@pf
where c.reviewed
group by 1,2;

-- Now we will calculate exposures * portfolio value for each reviewed company 3) of summary
insert into tmp_cid_values
select 
    t.cid,
    t.isin,
    true,
    sum( (d.sales/t.total_sales)*CSV_DOUBLE(s.exposure,1) )*p.val as s1,
    sum( (d.sales/t.total_sales)*CSV_DOUBLE(s.exposure,2) )*p.val as s2,
    sum( (d.sales/t.total_sales)*CSV_DOUBLE(s.exposure,3) )*p.val as s3,
    sum( (d.sales/t.total_sales)*CSV_DOUBLE(s.exposure,4) )*p.val as s4
from tmp_rv_total t
join sales_divdetails d on t.cid = d.cid and d.syear=@year
join sales_portfolio_data p on t.isin = p.isin and portfolio_id=@pf
join sales_sic s on d.sic=s.id
group by 1


select sum(val) from  sales_portfolio_data where portfolio_id=@pf and isin
in ( select isin from tmp_cid_values) into @divider;

-- We have all portfolie calculated values in tmp_cid_values We jast need sum all them and divide by divider
select 
    sum(p1)/@divider as s1,
    sum(p2)/@divider as s2,
    sum(p3)/@divider as s3,
    sum(p4)/@divider as s4
from tmp_cid_values

drop table subsector_nrw_values;
drop table subsector_nrw_total;
drop table tmp_cids;
drop table tmp_cid_values;
drop table tmp_rv_total;





select sum(d.sales)
from sales_companies c
join sales_divdetails d on c.cid = d.cid and d.syear=@year
where subsector='Biotechnology' ;

-- NEW FIXED CALCULATIONS --------------------------------------------------------------------------------------------
-- calc subsector values
CREATE TEMPORARY TABLE tmp_subsector_total (subsector  varchar(100), total double, index(subsector)) ENGINE=MEMORY;

insert into tmp_subsector_total
select c.subsector, sum(d.sales)
from sales_companies c
join sales_divdetails d on c.cid = d.cid and d.syear=@year
group by 1;


CREATE TEMPORARY TABLE tmp_subsector_values (subsector  varchar(100), p1 double, p2 double, p3 double, p4 double, index(subsector)) ENGINE=MEMORY;

insert into tmp_subsector_values
select 
    c.subsector,
    sum(d.sales*CSV_DOUBLE(s.exposure,1))/t.total as p1,
    sum(d.sales*CSV_DOUBLE(s.exposure,2))/t.total as p2,
    sum(d.sales*CSV_DOUBLE(s.exposure,3))/t.total as p3,
    sum(d.sales*CSV_DOUBLE(s.exposure,4))/t.total as p4
from sales_companies c
join sales_divdetails d on c.cid = d.cid and d.syear=@year
join sales_sic s on d.sic=s.id
join tmp_subsector_total t on c.subsector=t.subsector
group by 1;


CREATE TEMPORARY TABLE tmp_cids (cid varchar(16) NOT NULL, isin varchar(32),
 reviewed boolean, primary key (cid), index(isin))  ENGINE=MEMORY;

-- saving not reviewed rows
insert into tmp_cids
select 
    c.cid, c.isin, false
from sales_companies c
join sales_divdetails d on c.cid = d.cid and d.syear=@year
join sales_portfolio_data p on c.isin = p.isin and p.portfolio_id=@pf
where not c.reviewed
group by 1,2;

-- saving not reviewed rows
insert into tmp_cids
select 
    c.cid, c.isin, true
from sales_companies c
join sales_divdetails d on c.cid = d.cid and d.syear=@year
join sales_portfolio_data p on c.isin = p.isin and p.portfolio_id=@pf
where c.reviewed
group by 1,2;

-- calculate adjucted
select sum(p.val)
from tmp_cids t
join sales_portfolio_data p on t.isin = p.isin and portfolio_id=@pf
into @pfsum;

--calculate adjucted values
select t.isin, reviewed, p.val/@pfsum as adjucted
from tmp_cids t
join sales_portfolio_data p on t.isin = p.isin and portfolio_id=@pf


CREATE TEMPORARY TABLE tmp_fin_portfolio_values ( isin varchar(32),reviewed boolean, adjucted double, p1 double, p2 double, p3 double, p4 double, index(isin)) ENGINE=MEMORY;

-- insert not reviewed values
insert into tmp_fin_portfolio_values
select t.isin, c.reviewed, p.val/@pfsum as adjucted,
sv.p1,
sv.p2,
sv.p3,
sv.p4
from tmp_cids t
join sales_companies c on t.cid=c.cid and not c.reviewed
join tmp_subsector_values sv on c.subsector = sv.subsector
join sales_portfolio_data p on t.isin = p.isin and portfolio_id=@pf;

-- Calculations for the reviewed rows
select d.cid, d.me, d.sic, d.sales
,sum( d.sales*CSV_DOUBLE(s.exposure,1) )
,sum( d.sales*CSV_DOUBLE(s.exposure,2) )
,sum( d.sales*CSV_DOUBLE(s.exposure,3) )
,sum( d.sales*CSV_DOUBLE(s.exposure,4) )
from tmp_cids t 
join sales_divdetails d on t.cid=d.cid
join sales_sic s on d.sic=s.id
where t.reviewed and d.syear=@year and d.sales is not null;

-- Calculate total sales for each companie
CREATE TEMPORARY TABLE tmp_companie_total_sales ( cid varchar(16), total double, index(cid)) ENGINE=MEMORY;

insert into tmp_companie_total_sales
select 
  d.cid, 
  sum(d.sales) as total
from tmp_cids t
join sales_divdetails d on d.cid=t.cid and t.reviewed and d.syear=@year and d.sales is not null
group by 1;

-- Calculations for the reviewed rows
select 
 sum( d.sales*CSV_DOUBLE(s.exposure,1) ) / ts.total as t1
,sum( d.sales*CSV_DOUBLE(s.exposure,2) ) / ts.total as t2
,sum( d.sales*CSV_DOUBLE(s.exposure,3) ) / ts.total as t3
,sum( d.sales*CSV_DOUBLE(s.exposure,4) ) / ts.total as t4
from tmp_cids t 
join sales_divdetails d on t.cid=d.cid
join sales_sic s on d.sic=s.id
join tmp_companie_total_sales ts on t.cid = ts.cid
where t.reviewed and d.syear=@year and d.sales is not null;

-- insert reviewed finnaly values
insert into tmp_fin_portfolio_values
select t.isin, true as reviewed, p.val/@pfsum as adjucted
,sum( d.sales*CSV_DOUBLE(s.exposure,1) ) / ts.total as t1
,sum( d.sales*CSV_DOUBLE(s.exposure,2) ) / ts.total as t2
,sum( d.sales*CSV_DOUBLE(s.exposure,3) ) / ts.total as t3
,sum( d.sales*CSV_DOUBLE(s.exposure,4) ) / ts.total as t4
from tmp_cids t
join sales_divdetails d on t.cid=d.cid
join sales_sic s on d.sic=s.id
join tmp_companie_total_sales ts on t.cid = ts.cid
join sales_portfolio_data p on t.isin = p.isin and portfolio_id=@pf
where t.reviewed and d.syear=@year and d.sales is not null
group by 1,2,3;

-- find final values
select 
  sum(t.p1*t.adjucted) as e1,
  sum(t.p2*t.adjucted) as e2,
  sum(t.p3*t.adjucted) as e3,
  sum(t.p4*t.adjucted) as e4,
  count(*) as count
from tmp_fin_portfolio_values t

