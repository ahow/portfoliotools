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


select sum(CSV_DOUBLE(exposure,@theme_id)) from sales_sic 


select * sales_divdetails where sic in (116)

-- TEST
select s.name, d.me, d.sales, d.syear, CSV_DOUBLE(exposure,1) 
from sales_divdetails d
join sales_sic  s  ON d.sic = s.id
where d.sic=116;


select s.name, d.me, d.sales, d.syear  
from sales_divdetails d
join sales_sic  s  ON d.sic = s.id
where d.sic=116;

select s.id, s.name, d.me, d.sales, d.syear  
from sales_sic s
join sales_divdetails d  ON s.id = d.sic
where CSV_DOUBLE(s.exposure,1)=2;

set @max_year=2015;

select s.id, sum(d.sales)  
from sales_sic s
join sales_divdetails d  ON s.id = d.sic
where d.syear=2015 and CSV_DOUBLE(s.exposure,1)=2
group by 1;



CREATE TEMPORARY TABLE tmp_selected_sics (sic integer NOT NULL);
insert into tmp_selected_sics
select id
from sales_sic 
where id in (116,119,131) 
and id<>9999;
CREATE TEMPORARY TABLE tmp_cid_sales (cid varchar(16) NOT NULL, tsales double, primary key (cid)) ENGINE=MEMORY;


select d.sic, d.cid,  sum(d.sales)
from sales_divdetails d
join tmp_selected_sics t on d.sic = t.sic
where d.syear=@maxyear
group by 1, 2;


