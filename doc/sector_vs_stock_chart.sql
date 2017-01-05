-- getExposuresByPortfolio year=2015 portfolio=3

set @year=2015;
set @pf=3; -- 1a    5 is 2a
set @mt=3;

-- select metrics for calcilation of trimmean by sector
select d.col, d.val, c.sector
from sales_metrics_data d
join sales_companies c on d.isin = c.isin
where metric_id=@mt
order by 3;

-- select portfolio data for the calculations
select p.isin, p.val, c.sector, sum(d.val) as tmetric
from sales_portfolio_data p
join sales_companies c on p.isin = c.isin
join sales_metrics_data d on p.isin=d.isin and d.metric_id=@mt
where p.portfolio_id=@pf
group by 1


-- debug
CREATE TEMPORARY TABLE tmp_sector_stock 
(isin varchar(32) NOT NULL, val double, sector varchar(255), ttmetric double, primary key (isin))  ENGINE=MEMORY;

insert into tmp_sector_stock
select p.isin, p.val, c.sector, sum(d.val) as tmetric
from sales_portfolio_data p
join sales_companies c on p.isin = c.isin
join sales_metrics_data d on p.isin=d.isin and d.metric_id=@mt
where p.portfolio_id=@pf
group by 1;

select sum(p.val) 
from sales_portfolio_data p
where p.portfolio_id=@pf;
