-- getExposuresByPortfolio year=2015 portfolio=3

set @year=2015;
set @pf=4; -- 1a    5 is 2a
set @mt=3;

select d.col, d.val, c.sector
from sales_metrics_data d
join sales_companies c on d.isin = c.isin
where metric_id=@mt
order by 3;




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

-- saving reviewed rows
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

