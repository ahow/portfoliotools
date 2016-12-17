set @year=2015;
set @pf=4; 
set @mt=2;


CREATE TEMPORARY TABLE tmp_portfolio_wg (col smallint, total double, primary key (col))  ENGINE=MEMORY;

insert into tmp_portfolio_wg
select m.col, sum(p.val) as total
from sales_portfolio_data p
join sales_metrics_data m on p.isin=m.isin and m.metric_id=@mt
where portfolio_id=@pf
group by 1;

select m.col, c.name, sum(p.val*m.val)/t.total as s
from sales_portfolio_data p
join sales_metrics_data m on p.isin=m.isin and m.metric_id=@mt
join tmp_portfolio_wg t on m.col=t.col
join sales_metrics_columns c on c.metric_id=@mt and m.col=c.col
where portfolio_id=@pf
group by 1,2
order by 1;

drop table tmp_portfolio_wg;


-- Second chart (stacked with variable width of columns)

set @pf=3; 
set @mt=4;

select 
    p.isin, p.val, m.col, m.val
from sales_portfolio_data p
join sales_metrics_data m on p.isin=m.isin and m.metric_id=@mt
join sales_companies c on p.isin=c.isin 
where portfolio_id=@pf
order by p.isin, m.col;

