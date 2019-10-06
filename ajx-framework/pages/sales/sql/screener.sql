create temporary table tmp_theam_weights
(  theam_id integer not null,
   weight double precision,
   min_value integer,
   max_value integer,
   primary key (theam_id)
);

insert into tmp_theam_weights values ; --placeholder
--insert into tmp_theam_weights values
--(1, 20, -8, 5),
--(2, 30, -10, 8),
--(3, 50, -9, 9),
--(4, 0, -10, 10);


select sum(weight) from tmp_theam_weights
into @sum_weights;

create temporary table tmp_weight_theme_exps
select 
    c.cid, sum(t.theam_value*w.weight)/@sum_weights
         as weight_theme_exp
from sales_companies c
join sales_company_theams  t on c.cid = t.cid
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

-- Final query
select c.cid, c.name, e.weight_theme_exp, t.theam_id, t.theam_value $columns
from sales_companies c
join (
select 
    c.cid, count(*)
from sales_companies c
join sales_company_theams  t on c.cid = t.cid
join tmp_theam_weights  w on w.theam_id= t.theam_id 
                    and t.theam_value>=w.min_value 
                    and t.theam_value<=w.max_value
group by c.cid
having count(*)=4
) as sc on c.cid=sc.cid
join tmp_weight_theme_exps e on c.cid = e.cid
join sales_company_theams  t on c.cid = t.cid
$where;
