select max(syear) from sales_divdetails into @syear;

-- Calculation by weight described on CompanyThemeMetricsCalc sheet
select 
  ct.cid,
  ct.theam_id,
  ct.theam_value*th.SIC_weight/100 +
  COALESCE(tt.theam_value, ct.theam_value)*th.company_weight/100 as theam_value
from
( select 
    c.cid, st.theam_id, sum(d.sales/t.sales*st.theam_value) as theam_value
    from sales_companies_totals  c
    join sales_divdetails d on d.cid = c.cid and d.syear=@syear
    join sales_sic_theams st on d.sic = st.sic_id
    join sales_companies_totals t on c.cid=t.cid
    group by c.cid, st.theam_id
) as ct
join sales_theams th on ct.theam_id = th.id
left outer join sales_company_theams tt on ct.cid=tt.cid and ct.theam_id=tt.theam_id;

