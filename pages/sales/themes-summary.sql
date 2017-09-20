-- Themes summary
-- input: @theme_min @theme_max @theme_id

set @theme_min = 0;
set @theme_max = 1;
set @theme_id = 1;


select max(syear), min(syear) from sales_divdetails into @maxyear, @minyear;


CREATE TEMPORARY TABLE tmp_selected_sics (sic integer NOT NULL);

-- selecting SIC IDs by theme criteria abs theme ID
insert into tmp_selected_sics
select id
from sales_sic 
where CSV_DOUBLE(exposure,@theme_id)  between @theme_min and @theme_max
 -- remove Nonclassifiable Establishments 
and id<>9999;

-- select
select 
   t.sic, sum(d.sales) as tsales
from tmp_selected_sics t
join sales_divdetails d on t.sic=d.sic and syear=@maxyear and d.sales is not null
group by 1;

