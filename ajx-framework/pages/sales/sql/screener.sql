select max(syear) from sales_divdetails into @syear;


create temporary table tmp_combined_theams engine=Memory as 
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


-- because MySQL can't open temporary table twice in the query
create temporary table tmp_combined_theams2 engine Memory as
select * from tmp_combined_theams;

create temporary table tmp_theam_weights
(  theam_id integer not null,
   weight double precision,
   min_value integer,
   max_value integer,
   primary key (theam_id)
) engine=Memory;

insert into tmp_theam_weights values ; --placeholder
--insert into tmp_theam_weights values
--(1, 20, -8, 5),
--(2, 30, -10, 8),
--(3, 50, -9, 9),
--(4, 0, -10, 10);


select sum(weight) from tmp_theam_weights
into @sum_weights;

create temporary table tmp_weight_theme_exps engine=Memory
as
select 
    c.cid, sum(t.theam_value*w.weight)/@sum_weights
         as overall_theme_exp
from sales_companies c
join tmp_combined_theams as t on c.cid = t.cid
join tmp_theam_weights  w on w.theam_id= t.theam_id
where t.theam_id in (1,2,3)
group by c.cid;

-- to normalize values
select
    100/max(sales),
    100/max(market_cap),
    100/max(sales_growth),
    100/max(roic),
    100/max(pe),
    100/max(evebitda),
    100/max(EBITDA_growth),
    100/max(ROE),
    100/max(price_to_book),
    100/max(reinvestment),
    100/max(research_and_development),
    100/max(net_debt_to_EBITDA),
    100/max(CAPE),
    100/max(sustain_ex),
    100/max(yield) 
from sales_companies 
into @sales, @market_cap, @sales_growth, @roic, @pe, @evebitda,
@EBITDA_growth, @ROE, @price_to_book, @reinvestment, @research_and_development,
@net_debt_to_EBITDA, @CAPE, @sustain_ex, @yield;

select 100/max(overall_theme_exp) 
from tmp_weight_theme_exps into @overall_theme_exp;

-- Final query
select c.cid, c.name, e.overall_theme_exp, t.theam_id, t.theam_value $columns
from sales_companies c
join (
select 
    c.cid, count(*)
from sales_companies c
join tmp_combined_theams as  t on c.cid = t.cid
join tmp_theam_weights  w on w.theam_id= t.theam_id 
                    and t.theam_value>=w.min_value 
                    and t.theam_value<=w.max_value
group by c.cid
having count(*)=4
) as sc on c.cid=sc.cid
join tmp_weight_theme_exps e on c.cid = e.cid
join tmp_combined_theams2 as t on c.cid = t.cid
$where
order by sc.cid, t.theam_id;
