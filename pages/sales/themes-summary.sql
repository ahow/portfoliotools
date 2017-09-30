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

delimiter //
create procedure update_sales_totals_with_negative()
begin

    drop table if exists sales_companies_totals;
    drop table if exists sales_sic_companies_totals;
    
    -- create totals table
    CREATE TABLE IF NOT EXISTS sales_companies_totals
    ( cid varchar(16) NOT NULL,
      sales double precision not null,
      primary key (cid)
    );

    CREATE TABLE IF NOT EXISTS sales_sic_companies_totals
    ( cid varchar(16) NOT NULL,
      sic integer NOT NULL,
      psale double precision not null,
      index (sic),
      primary key (cid,sic)
    );
    


    select max(syear) from sales_divdetails into @max_year;

    insert into sales_companies_totals
    select 
       c.cid, sum(d.sales)
    from sales_divdetails d
    join sales_companies c on  d.cid = c.cid
    where d.syear=@max_year 
    group by 1;
    
    insert into sales_sic_companies_totals
    select 
        d.cid, d.sic, d.sales/t.sales*100 as psale
    from sales_divdetails d 
    join sales_companies c on d.cid=c.cid 
    join sales_companies_totals t on d.cid=t.cid 
    where d.syear=@max_year
    group by 1,2
    having psale is not null;

end//
delimiter ;

delimiter //
create procedure update_sales_totals()
begin

    drop table if exists sales_companies_totals;
    drop table if exists sales_sic_companies_totals;
    
    -- create totals table
    CREATE TABLE IF NOT EXISTS sales_companies_totals
    ( cid varchar(16) NOT NULL,
      sales double precision not null,
      primary key (cid)
    );

    CREATE TABLE IF NOT EXISTS sales_sic_companies_totals
    ( cid varchar(16) NOT NULL,
      sic integer NOT NULL,
      psale double precision not null,
      index (sic),
      primary key (cid,sic)
    );
    


    select max(syear) from sales_divdetails into @max_year;

    insert into sales_companies_totals
    select 
       c.cid, sum(d.sales)
    from sales_divdetails d
    join sales_companies c on  d.cid = c.cid
    where d.syear=@max_year and d.sales>0 
    group by 1;
    
    insert into sales_sic_companies_totals
    select 
        d.cid, d.sic, d.sales/t.sales*100 as psale
    from sales_divdetails d 
    join sales_companies c on d.cid=c.cid 
    join sales_companies_totals t on d.cid=t.cid 
    where d.syear=@max_year and d.sales>0
    group by 1,2
    having psale is not null;

end//
delimiter ;



--, c.market_cap,c.sales_growth,
--c.roic, c.pe, c.evebitda, c.payout, c.reviewed

select 
  p.sic,
  sum(d.sales) as tsales,
  sum(c.roic*p.psale)/sum(p.psale) as aroic,
  sum(c.pe*p.psale)/sum(p.psale) as ape,
  sum(c.evebitda*p.psale)/sum(p.psale) as aevebitda,
  sum(c.payout*p.psale)/sum(p.psale) as apayout
from sales_divdetails d
join sales_companies c on  d.cid = c.cid
join sales_sic_companies_totals p on d.cid=p.cid and d.sic=p.sic
where d.syear=@max_year and d.sic<300 and d.sic>0
group by d.sic;


select 
  sum(d.sales) as tsales,
  sum(c.roic*p.psale)/sum(p.psale) as aroic,
  sum(c.pe*p.psale)/sum(p.psale) as ape,
  sum(c.evebitda*p.psale)/sum(p.psale) as aevebitda,
  sum(c.payout*p.psale)/sum(p.psale) as apayout
from sales_divdetails d
join sales_companies c on  d.cid = c.cid
join sales_sic_companies_totals p on d.cid=p.cid and d.sic=p.sic
where d.syear=@max_year and  d.sic in (116,119,131);
;


-- Это правильный запрос:
select 
  p.sic,
  sum(d.sales) as tsales,
  sum(c.sales_growth*p.psale)/sum(p.psale) as asales_growth,
  sum(c.roic*p.psale)/sum(p.psale) as aroic,
  sum(c.pe*p.psale)/sum(p.psale) as ape,
  sum(c.evebitda*p.psale)/sum(p.psale) as aevebitda,
  sum(c.payout*p.psale)/sum(p.psale) as apayout
from sales_divdetails d
join sales_companies c on  d.cid = c.cid
join sales_sic_companies_totals p on d.cid=p.cid and d.sic=p.sic
where d.syear=@max_year and d.sales>0 and d.sic in (116,119,131)
group by d.sic;

-- Новый вариант расчёта
select 
  p.sic,
  sum(d.sales) as tsales,
  sum(c.roic*p.psale*t.sales)/sum(p.psale*t.sales) as aroic,
  sum(c.pe*p.psale*t.sales)/sum(p.psale*t.sales) as ape,
  sum(c.evebitda*p.psale*t.sales)/sum(p.psale*t.sales) as aevebitda,
  sum(c.payout*p.psale*t.sales)/sum(p.psale*t.sales) as apayout
from sales_divdetails d
join sales_companies c on  d.cid = c.cid
join sales_sic_companies_totals p on d.cid=p.cid and d.sic=p.sic
join sales_companies_totals t on d.cid=t.cid
where d.syear=@max_year and d.sales>0 and d.sic in (116,119,131)
group by d.sic;


-- Теперь мы можем вычислить по теме
set @theme_min = 2;
set @theme_max = 2;
set @theme_id = 1;
set @max_year = 2015;
CREATE TEMPORARY TABLE IF NOT EXISTS tmp_selected_sics (sic integer NOT NULL);
insert into tmp_selected_sics
select id
from sales_sic 
where CSV_DOUBLE(exposure,@theme_id)  between @theme_min and @theme_max
and id<>9999;

select 
  sum(st.tsales*st.aroic)/sum(st.tsales) as roic,
  sum(st.tsales*st.ape)/sum(st.tsales) as pe,
  sum(st.tsales*st.aevebitda)/sum(st.tsales) as evebitda,
  sum(st.tsales*st.apayout)/sum(st.tsales) as payout
from (
select 
  p.sic,
  sum(d.sales) as tsales,
  sum(c.roic*p.psale)/sum(p.psale) as aroic,
  sum(c.pe*p.psale)/sum(p.psale) as ape,
  sum(c.evebitda*p.psale)/sum(p.psale) as aevebitda,
  sum(c.payout*p.psale)/sum(p.psale) as apayout
from sales_divdetails d
join sales_companies c on  d.cid = c.cid
join tmp_selected_sics ss on d.sic=ss.sic
join sales_sic_companies_totals p on d.cid=p.cid and d.sic=p.sic
where d.syear=@max_year and d.sales>0
group by d.sic
) as st;
