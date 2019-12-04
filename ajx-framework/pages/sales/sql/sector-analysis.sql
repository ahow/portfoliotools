create temporary table tmp_combined_theams engine=Memory as 
select 
  ct.cid,
  ct.theam_id,
  ct.theam_value*th.SIC_weight/100 +
  COALESCE(tt.theam_value, ct.theam_value)*th.company_weight/100 as theam_value
from
( select 
    c.cid, st.theam_id, sum(d.sales/t.sales*st.theam_value) as theam_value
    from sales_companies_totals c
    join tmp_sector_anlysis sc on c.cid = sc.cid
    join sales_divdetails d on d.cid = c.cid and d.syear=@syear
    join sales_sic_theams st on d.sic = st.sic_id
    join sales_companies_totals t on c.cid=t.cid
    group by c.cid, st.theam_id
) as ct
join sales_theams th on ct.theam_id = th.id
left outer join sales_company_theams tt on ct.cid=tt.cid and ct.theam_id=tt.theam_id;


select 
    sum(c.market_cap)
from sales_companies c
join tmp_sector_anlysis a on c.cid=a.cid
into @total_market_cap;

create temporary table tmp_themes_chart engine=Memory as 
select 
    t.theam_id, sum(c.market_cap/@total_market_cap*t.theam_value) as theam_value
from tmp_combined_theams t
join tmp_sector_anlysis a on t.cid=a.cid
join sales_companies c on a.cid = c.cid
group by t.theam_id;
