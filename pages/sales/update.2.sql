-- for update: update  mc_pages set update_no=1 where name='sales'
-- or open URL http://sales.loc/setup?sales=1
drop procedure if exists update_sales_totals;
drop procedure if exists select_sics_by_theme_range;
drop procedure if exists select_single_sic;
drop procedure if exists summary_by_sics;
drop procedure if exists summary_by_sics_by_years;
drop procedure if exists test_proc;
drop procedure if exists get_stability;
drop procedure if exists get_stability_by_years;

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

-- input:  tmp_selected_sics must exists before call it
create procedure get_stability(I_max_year int, I_region varchar(255),
OUT stability DOUBLE)
begin
    DECLARE i,r,gr INT;
    /* DECLARE L_min_year, L_max_year INT; */
        
    DROP TABLE IF EXISTS tmp_companies_totals_by_year;    

    CREATE TEMPORARY TABLE tmp_companies_totals_by_year 
    (syear integer, cid varchar(16) NOT NULL,
    tsum double, PRIMARY KEY (cid, syear));

    DROP TABLE IF EXISTS tmp_companies_rank1;
    DROP TABLE IF EXISTS tmp_companies_rank2;

    CREATE TEMPORARY TABLE tmp_companies_rank1
    (syear integer, cid varchar(16) NOT NULL,
    rank integer, PRIMARY KEY (cid, syear));

    CREATE TEMPORARY TABLE tmp_companies_rank2
    (syear integer, cid varchar(16) NOT NULL,
    rank integer, PRIMARY KEY (cid, syear));

    /*  
    select 
        min(d.syear) as minyear, max(d.syear) as maxyear
    from sales_divdetails d
    into L_min_year, L_max_year;
    */
    
    SET i = I_max_year-1;
 
    WHILE i<=I_max_year DO
        set @n = 1;
        IF I_region='' or I_region='Global' THEN
            insert into tmp_companies_totals_by_year
            select 
            d.syear, d.cid, sum(d.sales)
            from sales_divdetails d
            join tmp_selected_sics ss on d.sic=ss.sic              
            where d.syear=i and d.sales is not null
            group by 1,2
            order by 1, 3 desc
            limit 20;
        ELSE
            insert into tmp_companies_totals_by_year
            select 
            d.syear, d.cid, sum(d.sales)
            from sales_divdetails d        
            join tmp_selected_sics ss on d.sic=ss.sic
            join sales_companies c on  d.cid = c.cid
            where d.syear=i and d.sales is not null and c.region=I_region
            group by 1,2
            order by 1, 3 desc
            limit 20;
        END IF;
       SET i=i+1; 
    END WHILE;
    
    set @n = 0; 
    set @gr=null;
    -- inserting data for
    insert into tmp_companies_rank1 
    select r.syear, r.cid, r.rank from
    (
    select 
        cid, 
        syear,
        (@n:=if(@gr=syear,@n+1,1)) as rank,
        @gr:=syear 
    from tmp_companies_totals_by_year
    order by syear desc, tsum desc
    ) as r;
  
    -- create the copy of rank because MySQL can't use join to
    -- the temporary table itself
    insert into tmp_companies_rank2
    select r.syear, r.cid, r.rank from tmp_companies_rank1 r;
    
    -- getting stability
    select avg(r.cdif) from
    (   select 
           t.cid, t.syear, t.rank as rank, p.rank as prank,
           (t.rank-p.rank) as diff,
           if(abs(t.rank-p.rank)>5,5,abs(t.rank-p.rank)) as cdif
        from tmp_companies_rank1 t
        left outer join tmp_companies_rank2 p on t.cid=p.cid 
            and p.syear=(t.syear-1)
        order by t.cid, t.syear desc
    ) as r into stability;
end$$


-- input:  tmp_selected_sics must exists before call it
create procedure get_stability_by_years(I_region varchar(255))
begin
    DECLARE i,r,gr INT;
    DECLARE L_min_year, L_max_year INT;
        
    DROP TABLE IF EXISTS tmp_companies_totals_by_year;    

    CREATE TEMPORARY TABLE tmp_companies_totals_by_year 
    (syear integer, cid varchar(16) NOT NULL,
    tsum double, PRIMARY KEY (cid, syear));

    DROP TABLE IF EXISTS tmp_companies_rank1;
    DROP TABLE IF EXISTS tmp_companies_rank2;

    CREATE TEMPORARY TABLE tmp_companies_rank1
    (syear integer, cid varchar(16) NOT NULL,
    rank integer, PRIMARY KEY (cid, syear));

    CREATE TEMPORARY TABLE tmp_companies_rank2
    (syear integer, cid varchar(16) NOT NULL,
    rank integer, PRIMARY KEY (cid, syear));

      
    select 
        min(d.syear) as minyear, max(d.syear) as maxyear
    from sales_divdetails d
    into L_min_year, L_max_year;
    
    
    SET i = L_min_year;
 
    WHILE i<=L_max_year DO
        set @n = 1;
        IF I_region='' or I_region='Global' THEN
            insert into tmp_companies_totals_by_year
            select 
            d.syear, d.cid, sum(d.sales)
            from sales_divdetails d
            join tmp_selected_sics ss on d.sic=ss.sic              
            where d.syear=i and d.sales is not null
            group by 1,2
            order by 1, 3 desc
            limit 20;
        ELSE
            insert into tmp_companies_totals_by_year
            select 
            d.syear, d.cid, sum(d.sales)
            from sales_divdetails d        
            join tmp_selected_sics ss on d.sic=ss.sic
            join sales_companies c on  d.cid = c.cid
            where d.syear=i and d.sales is not null and c.region=I_region
            group by 1,2
            order by 1, 3 desc
            limit 20;
        END IF;
       SET i=i+1; 
    END WHILE;
    
    set @n = 0; 
    set @gr=null;
    -- inserting data for
    insert into tmp_companies_rank1 
    select r.syear, r.cid, r.rank from
    (
    select 
        cid, 
        syear,
        (@n:=if(@gr=syear,@n+1,1)) as rank,
        @gr:=syear 
    from tmp_companies_totals_by_year
    order by syear desc, tsum desc
    ) as r;
  
    -- create the copy of rank because MySQL can't use join to
    -- the temporary table itself
    insert into tmp_companies_rank2
    select r.syear, r.cid, r.rank from tmp_companies_rank1 r;
    
    -- getting stability
    
    select r.syear, avg(r.cdif) as v from
    (   select 
           t.cid, t.syear, t.rank as rank, p.rank as prank,
           (t.rank-p.rank) as diff,
           if(abs(t.rank-p.rank)>5,5,abs(t.rank-p.rank)) as cdif
        from tmp_companies_rank1 t
        left outer join tmp_companies_rank2 p on t.cid=p.cid 
            and p.syear=(t.syear-1)
        order by t.cid, t.syear desc
    ) as r 
    group by 1
    order by 1;
    
end$$



create procedure summary_by_sics_by_years(I_funct VARCHAR(20), I_region varchar(255))
begin
    CASE I_funct
    WHEN 'stability' THEN call get_stability_by_years(I_region);      
    ELSE
      BEGIN
        select -1 as syear, null as v;
      END;
    END CASE;    
end$$

create procedure select_single_sic(I_sic integer)
begin
    DROP TABLE IF EXISTS tmp_selected_sics;
    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_selected_sics (sic integer NOT NULL);
    insert into tmp_selected_sics values (I_sic);
end$$

create procedure select_sics_by_theme_range(I_max_year integer,
 I_theme_id integer, I_theme_min integer, I_theme_max integer,
 I_region varchar(255))
 
begin
    DROP TABLE IF EXISTS tmp_selected_sics;
    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_selected_sics (sic integer NOT NULL);

    IF I_region='' or I_region='Global' THEN
        insert into tmp_selected_sics
        select id
        from sales_sic 
        where CSV_DOUBLE(exposure,I_theme_id)  between I_theme_min and I_theme_max
        and id<>9999;
    ELSE
      insert into tmp_selected_sics
        select distinct s.id
          from sales_divdetails d
        join sales_companies c on  d.cid = c.cid
        join sales_sic s on d.sic=s.id
        where d.syear=I_max_year and d.sales>0 
        and CSV_DOUBLE(s.exposure,I_theme_id) between I_theme_min and I_theme_max 
        and s.id<>9999
        and c.region=I_region
        order by s.id;
    END IF;
end$$

/*
call selected_sics_by...
before using

set @year = 2015;
-- Theme A
set @theme_id = 1;
set @theme_min = -1;
set @theme_max = -0;
-- Global region
set @region = '';
call select_sics_by_theme_range(@year, @theme_id, @theme_min, @theme_max, @region);

-- Selected SICs:
-- select * from tmp_selected_sics;

call get_stability(@year, @region, @stab);
select @stab;

-- Debug stability:
select 
   t.cid, t.syear, t.rank as rank, p.rank as prank,
   (t.rank-p.rank) as diff,
   if(abs(t.rank-p.rank)>5,5,abs(t.rank-p.rank)) as cdif
from tmp_companies_rank1 t
left outer join tmp_companies_rank2 p on t.cid=p.cid 
    and p.syear=(t.syear-1)
order by t.cid, t.syear desc;
        

call summary_by_sics(@year, @name, @region);
*/
create procedure summary_by_sics(I_max_year integer, I_name varchar(255),
I_region varchar(255))
begin
    DECLARE L_top3sum DOUBLE;
    DECLARE L_top5sum DOUBLE;
    DECLARE L_previewed DOUBLE;
    DECLARE L_stability DOUBLE;
  
    -- select procent of revieved
    select 
       100*sum(ct.reviewed)/count(*) as prewiewed
    from 
    (  select 
        d.cid, c.reviewed
        from sales_divdetails d
        join sales_companies c on  d.cid = c.cid
        join tmp_selected_sics ss on d.sic=ss.sic
        where d.syear=I_max_year
        group by 1,2
    ) as ct into L_previewed;
    
     -- select top 3
     select sum(t3.sales) from
     (select 
        d.cid, d.sales
        from sales_divdetails d
        join sales_companies c on  d.cid = c.cid
        join tmp_selected_sics ss on d.sic=ss.sic
        where d.syear=I_max_year
     order by 2 desc
     limit 3) as t3 into L_top3sum;

     -- select top 5
     select sum(t5.sales) from
     (select 
        d.cid, d.sales
        from sales_divdetails d
        join sales_companies c on  d.cid = c.cid
        join tmp_selected_sics ss on d.sic=ss.sic
        where d.syear=I_max_year
     order by 2 desc
     limit 5) as t5 into L_top5sum;  
     
    -- select stability
    call get_stability(I_max_year, I_region, L_stability);

    -- select theme values
    select 
      I_name as name,
      L_top3sum as top3sum,
      L_top5sum as top5sum,
      L_stability as stability,
      sum(st.tsales) as tsales,
      sum(st.tsales*st.asales_growth)/sum(st.tsales) as asales_growth,
      sum(st.tsales*st.aroic)/sum(st.tsales) as aroic,
      sum(st.tsales*st.ape)/sum(st.tsales) as ape,
      sum(st.tsales*st.aevebitda)/sum(st.tsales) as aevebitda,
      sum(st.tsales*st.apayout)/sum(st.tsales) as apayout,
      L_previewed as previewed
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
    where d.syear=I_max_year and d.sales>0
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
