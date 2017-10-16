set @year=2015;
set @pf=5;
set @mt=5;


select d.isin, d.val, c.name, sum(m.val) as mval
 from sales_portfolio_data d
join sales_companies c on d.isin=c.isin
join sales_metrics_data m on m.isin=c.isin and m.metric_id=@mt
where d.portfolio_id=@pf
group by 1,2,3
