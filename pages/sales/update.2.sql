-- for update: update  mc_pages set update_no=1 where name='sales'
-- or open URL http://sales.loc/setup?sales=1
drop procedure if exists update_sales_totals;
drop procedure if exists select_themes_summary;
drop procedure if exists test_proc;

delimiter $$
-- This procedure must be loaded after uploading of divdetails
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

end$$


/*
    set @theme_min = 2;
    set @theme_max = 2;
    set @theme_id = 1;
    set @max_year = 2015;
    set @region = 'MENA';
*/
create procedure select_themes_summary()
begin
    DECLARE L_top3sum DOUBLE;
    DECLARE L_top5sum DOUBLE;
    DECLARE L_previewed DOUBLE;
    
    DROP TABLE IF EXISTS tmp_selected_sics;
    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_selected_sics (sic integer NOT NULL);

    IF @region='' or @region='Global' THEN
        insert into tmp_selected_sics
        select id
        from sales_sic 
        where CSV_DOUBLE(exposure,@theme_id)  between @theme_min and @theme_max
        and id<>9999;
    ELSE
      insert into tmp_selected_sics
        select distinct s.id
          from sales_divdetails d
        join sales_companies c on  d.cid = c.cid
        join sales_sic s on d.sic=s.id
        where d.syear=@max_year and d.sales>0 
        and CSV_DOUBLE(s.exposure,@theme_id) between @theme_min and @theme_max 
        and s.id<>9999
        and c.region=@region
        order by s.id;
    END IF;
  
    -- select procent of revieved
    select 
       100*sum(ct.reviewed)/count(*) as prewiewed
    from 
    (  select 
        d.cid, c.reviewed
        from sales_divdetails d
        join sales_companies c on  d.cid = c.cid
        join tmp_selected_sics ss on d.sic=ss.sic
        where d.syear=@max_year
        group by 1,2
    ) as ct into L_previewed;
    
     -- select top 3
     select sum(t3.sales) from
     (select 
        d.cid, d.sales
        from sales_divdetails d
        join sales_companies c on  d.cid = c.cid
        join tmp_selected_sics ss on d.sic=ss.sic
        where d.syear=@max_year
     order by 2 desc
     limit 3) as t3 into L_top3sum;

     -- select top 5
     select sum(t5.sales) from
     (select 
        d.cid, d.sales
        from sales_divdetails d
        join sales_companies c on  d.cid = c.cid
        join tmp_selected_sics ss on d.sic=ss.sic
        where d.syear=@max_year
     order by 2 desc
     limit 5) as t5 into L_top5sum;    

    -- select theme values
    select 
      @theme_id as name,
      L_top3sum as top3sum,
      L_top5sum as top5sum,
      L_previewed as previewed,
      sum(st.tsales) as tsales,
      sum(st.tsales*st.asales_growth)/sum(st.tsales) as asales_growth,
      sum(st.tsales*st.aroic)/sum(st.tsales) as aroic,
      sum(st.tsales*st.ape)/sum(st.tsales) as ape,
      sum(st.tsales*st.aevebitda)/sum(st.tsales) as aevebitda,
      sum(st.tsales*st.apayout)/sum(st.tsales) as apayout
    from (
    select 
      p.sic,
      sum(d.sales) as tsales,
      sum(c.sales_growth*p.psale*t.sales)/sum(p.psale*t.sales) as asales_growth,
      sum(c.roic*p.psale*t.sales)/sum(p.psale*t.sales) as aroic,
      sum(c.pe*p.psale*t.sales)/sum(p.psale*t.sales) as ape,
      sum(c.evebitda*p.psale*t.sales)/sum(p.psale*t.sales) as aevebitda,
      sum(c.payout*p.psale*t.sales)/sum(p.psale*t.sales) as apayout
    from sales_divdetails d
    join sales_companies c on  d.cid = c.cid
    join tmp_selected_sics ss on d.sic=ss.sic
    join sales_sic_companies_totals p on d.cid=p.cid and d.sic=p.sic
    join sales_companies_totals t on d.cid=t.cid
    where d.syear=@max_year and d.sales>0
    group by d.sic
    ) as st;

end$$

create procedure test_proc()
begin
    DECLARE i INT;
    
    select 
        min(d.syear) as minyear, max(d.syear) as maxyear
    from sales_divdetails d
    into @min_year, @max_year;
    SET i =  @min_year;
    DROP TABLE IF EXISTS tmp_all_years;
    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_all_years (syear integer NOT NULL);
    WHILE i<=@max_year DO
        insert into tmp_all_years values (i);
        SET i=i+1;
    END WHILE;
    -- select @min_year, @max_year;
    select * from tmp_all_years;
end$$

delimiter ;

call update_sales_totals;
