-- for update: update  mc_pages set update_no=1 where name='sales'
-- or open URL http://sales.loc/setup?sales=1
drop procedure if exists update_sales_totals;
drop procedure if exists select_sics_by_theme_range;
drop procedure if exists select_single_sic;
drop procedure if exists summary_by_sics;
drop procedure if exists summary_by_sics_by_years;
drop procedure if exists get_stability;
drop procedure if exists get_stability_by_years;
drop procedure if exists get_sics_stabilities;
drop procedure if exists get_sics_totals;
drop procedure if exists get_topN_by_years;
drop procedure if exists get_sics_totals_tmp;
drop procedure if exists get_topN_by_sic_years;
drop procedure if exists get_calc_by_years;
drop procedure if exists select_sics_by_themes;
drop procedure if exists selectCustomSics;
drop procedure if exists sales_growth_by_year;
drop procedure if exists select_companies_by_theme_range;
drop procedure if exists get_all_sics_stabilities;

delimiter $$
-- This procedure must be loaded after uploading of divdetails
create procedure update_sales_totals()
begin
    DECLARE max_year INT;
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
    


    select max(syear) from sales_divdetails into max_year;

    insert into sales_companies_totals
    select 
       c.cid, sum(d.sales)
    from sales_divdetails d
    join sales_companies c on  d.cid = c.cid
    where d.syear=max_year and d.sales>0 
    group by 1;
    
    insert into sales_sic_companies_totals
    select 
    d.cid, d.sic, sum(d.sales)/t.sales*100 as psale
    from sales_divdetails d 
    join sales_companies c on d.cid=c.cid 
    join sales_companies_totals t on d.cid=t.cid 
    where d.syear=max_year and d.sales>0
    group by 1,2,t.sales
    having psale is not null;
end $$

-- input:  tmp_selected_sics must exists before call it
create procedure get_stability(I_max_year int, I_region varchar(255),
OUT stability DOUBLE)
begin
    DECLARE i,r INT;
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
end $$

-- The function working with group of selected sics
-- OUT: tmp_stabilities table
-- DEBUG: select syear, sic, cid, sum(sales) from sales_divdetails where syear in (2014,2015) and sic=2821 group by 1,2,3 order by 1,2,4 desc;
create procedure get_sics_stabilities(I_year integer, I_region varchar(255))
begin
  DECLARE i,r INT;
    /* DECLARE L_min_year, L_max_year INT; */
    DROP TABLE IF EXISTS tmp_companies_sic_rank1;    
    DROP TABLE IF EXISTS tmp_companies_sic_rank2;
    
    CREATE TEMPORARY TABLE tmp_companies_sic_rank1
    (syear integer, sic integer not null, cid varchar(16) NOT NULL,
     tsales double, rank integer, PRIMARY KEY (sic, cid, syear));

    CREATE TEMPORARY TABLE tmp_companies_sic_rank2
    (syear integer, sic integer not null, cid varchar(16) NOT NULL,
     tsales double, rank integer, PRIMARY KEY (sic, cid, syear));
   

    set @gr=null;
    set @yr=null;
    set @n=0;

    insert into tmp_companies_sic_rank1
    select
     g.syear,
     g.sic,
     g.cid,
     g.tsales,
     g.rank
    from
    (   select
            s.syear,
            s.cid,
            s.tsales,
            (@n:=if(@gr=s.sic and @yr=s.syear,@n+1,1)) as rank,  
            @gr:=s.sic as sic,
            @yr:=s.syear as yr
        from 
        (select 
           d.syear, d.sic, d.cid, sum(d.sales) as tsales
        from sales_divdetails d
           join sales_companies c on  d.cid = c.cid
           join tmp_selected_sics ss on d.sic=ss.sic           
           where d.syear in (I_year-1, I_year) 
                and d.sales>0
                and (I_region='' or I_region='Global' or c.region=I_region)
           group by 1,2,3
           order by d.syear, d.sic, 4 desc       
        ) as s
    ) as g
    where g.rank<=20;

  
    insert into tmp_companies_sic_rank2
    select r.syear, r.sic, r.cid, r.tsales, r.rank from tmp_companies_sic_rank1 r;

    DROP TABLE IF EXISTS tmp_stabilities;
    
    CREATE TEMPORARY TABLE tmp_stabilities
    (sic integer not null, stability double,
     PRIMARY KEY (sic));
    
    -- getting stabilities
    insert into tmp_stabilities
    select r.sic, avg(r.cdif) as stability
    from
    (   select 
           t.sic, t.cid, t.syear, t.rank as rank, p.rank as prank,
           (t.rank-p.rank) as diff,
           if(abs(t.rank-p.rank)>5,5,abs(t.rank-p.rank)) as cdif
        from tmp_companies_sic_rank1 t
        left outer join tmp_companies_sic_rank2 p on t.sic=p.sic and t.cid=p.cid              
            and p.syear=(t.syear-1)
        order by t.cid, t.syear desc
    ) as r 
    group by r.sic;
    
end $$


create procedure get_all_sics_stabilities(I_year integer, I_region varchar(255))
begin
  DECLARE i,r INT;
    /* DECLARE L_min_year, L_max_year INT; */
    DROP TABLE IF EXISTS tmp_companies_sic_rank1;    
    DROP TABLE IF EXISTS tmp_companies_sic_rank2;
    
    CREATE TEMPORARY TABLE tmp_companies_sic_rank1
    (syear integer, sic integer not null, cid varchar(16) NOT NULL,
     tsales double, rank integer, PRIMARY KEY (sic, cid, syear));

    CREATE TEMPORARY TABLE tmp_companies_sic_rank2
    (syear integer, sic integer not null, cid varchar(16) NOT NULL,
     tsales double, rank integer, PRIMARY KEY (sic, cid, syear));
   

    set @gr=null;
    set @yr=null;
    set @n=0;

    insert into tmp_companies_sic_rank1
    select
     g.syear,
     g.sic,
     g.cid,
     g.tsales,
     g.rank
    from
    (   select
            s.syear,
            s.cid,
            s.tsales,
            (@n:=if(@gr=s.sic and @yr=s.syear,@n+1,1)) as rank,  
            @gr:=s.sic as sic,
            @yr:=s.syear as yr
        from 
        (select 
           d.syear, d.sic, d.cid, sum(d.sales) as tsales
        from sales_divdetails d
           join sales_companies c on  d.cid = c.cid         
           where d.syear in (I_year-1, I_year) 
                and d.sales>0
                and (I_region='' or I_region='Global' or c.region=I_region)
           group by 1,2,3
           order by d.syear, d.sic, 4 desc       
        ) as s
    ) as g
    where g.rank<=20;

  
    insert into tmp_companies_sic_rank2
    select r.syear, r.sic, r.cid, r.tsales, r.rank from tmp_companies_sic_rank1 r;

    DROP TABLE IF EXISTS tmp_stabilities;
    
    CREATE TEMPORARY TABLE tmp_stabilities
    (sic integer not null, stability double,
     PRIMARY KEY (sic));
    
    -- getting stabilities
    insert into tmp_stabilities
    select r.sic, avg(r.cdif) as stability
    from
    (   select 
           t.sic, t.cid, t.syear, t.rank as rank, p.rank as prank,
           (t.rank-p.rank) as diff,
           if(abs(t.rank-p.rank)>5,5,abs(t.rank-p.rank)) as cdif
        from tmp_companies_sic_rank1 t
        left outer join tmp_companies_sic_rank2 p on t.sic=p.sic and t.cid=p.cid              
            and p.syear=(t.syear-1)
        order by t.cid, t.syear desc
    ) as r 
    group by r.sic;
    
end $$

-- input:  tmp_selected_sics must exists before call it
-- rewrite it
create procedure get_stability_by_years(I_region varchar(255))
begin
    DECLARE i INT;
    DECLARE L_min_year, L_max_year INT;
        
    DROP TABLE IF EXISTS tmp_stab_by_years;    

    CREATE TEMPORARY TABLE tmp_stab_by_years
    (syear integer, v double, PRIMARY KEY (syear));
    
    select max(d.syear),min(d.syear) 
        from sales_divdetails d
        join sales_companies c on  d.cid = c.cid
        join tmp_selected_sics ss on d.sic=ss.sic
    where d.sales>0 and (I_region='' or I_region='Global' or c.region=I_region)
    into L_max_year, L_min_year;
    
    SET i = L_min_year;
 
    WHILE i<=L_max_year DO
        call get_sics_stabilities(i,I_region);
        
        insert into tmp_stab_by_years
        select
          i as syear, sum(astab)/sum(tsale) as v
        from
        (
            select
                d.sic, 
                sum(d.sales) as tsale,
                s.stability*sum(d.sales) as astab
            from sales_divdetails d 
              join sales_companies c on  d.cid = c.cid
              join tmp_selected_sics ss on d.sic=ss.sic
              join tmp_stabilities s on d.sic = s.sic             
            where d.syear=i and d.sales>0
                and (I_region='' or I_region='Global' or c.region=I_region)
            group by 1
        ) as r;
        
        SEt i = i+1;
    END WHILE;
    select * from tmp_stab_by_years;
end $$

create procedure get_sics_totals(I_region varchar(255))
begin    
    select 
        d.syear, sum(d.sales) as v
    from sales_divdetails d
      join sales_companies c on  d.cid = c.cid
      join tmp_selected_sics ss on d.sic=ss.sic           
    where 
        d.sales>0
        and (I_region='' or I_region='Global' or c.region=I_region)
   group by 1
   order by 1;
end$$

create procedure get_sics_totals_tmp(I_year integer, I_region varchar(255))
begin    
    DROP TABLE IF EXISTS tmp_total_sic_tsales_by_years;   
    CREATE TEMPORARY TABLE tmp_total_sic_tsales_by_years
        (syear integer, sic integer, tsum double, PRIMARY KEY (syear, sic));
     
    insert into tmp_total_sic_tsales_by_years 
    select 
        d.syear, d.sic, sum(d.sales) as v
    from sales_divdetails d
      join sales_companies c on  d.cid = c.cid
      join tmp_selected_sics ss on d.sic=ss.sic           
    where 
        d.sales>0
        and (I_region='' or I_region='Global' or c.region=I_region)
        and (I_year is null or d.syear=I_year)
   group by 1,2
   order by 1,2;
end $$

-- This procedure returns top N sales by selected sics by years
-- If I_year is not null then tops getting only for I_year
create procedure get_topN_by_sic_years(I_N integer, I_year int, I_region varchar(255))
begin
    set @n=0;
    set @gr=NULL;
    set @sc=NULL;
    
    DROP TABLE IF EXISTS tmp_totalN_by_sic_years; 
    CREATE TEMPORARY TABLE tmp_totalN_by_sic_years
        (syear integer, sic integer, proc double, PRIMARY KEY (syear, sic));
        
    call get_sics_totals_tmp(I_year, I_region);
    
    insert into tmp_totalN_by_sic_years
    select r2.syear, r2.sic, sum(r2.tsum)*100/t.tsum as v
    from
    ( select
           @n:=if(@gr=r.syear and @sc=r.sic,@n+1,1) as rank,
           @gr:=r.syear as syear,
           @sc:=r.sic as sic,
           r.cid,
           r.tsum
        from
        (select     
           d.syear,
           d.sic,
           d.cid,
           sum(d.sales) as tsum
            from sales_divdetails d
            join sales_companies c on  d.cid=c.cid
            join tmp_selected_sics s on d.sic=s.sic
        where d.sales>0 
            and (I_region='' or I_region='Global' or c.region=I_region)
            and (I_year is null or d.syear=I_year)
        group by d.syear, d.sic, d.cid
        order by d.syear, d.sic, tsum desc
        ) as r
    ) as r2
        join tmp_total_sic_tsales_by_years  t 
            on r2.syear=t.syear and r2.sic=t.sic
    where rank<=I_N
    group by r2.syear, r2.sic, t.tsum;
end $$

create procedure get_topN_by_years(I_N int, I_year int, I_region varchar(255))
begin
    call get_topN_by_sic_years(I_N,I_year,I_region);
    select p.syear, sum(p.proc*t.tsum)/sum(t.tsum) as v        
    from tmp_totalN_by_sic_years p
    join tmp_total_sic_tsales_by_years t
        on p.syear=t.syear and p.sic=t.sic
    group by p.syear;
end$$

-- calculations of: sales_growth, ROIC, PE, EVEBITDA, Payout
create procedure get_calc_by_years(I_year int, I_region varchar(255))
begin
   select 
      st.syear,
      sum(st.tsales) as tsales,
      sum(st.tsales*st.asales_growth)/sum(st.tsales) as sales_growth,
      sum(st.tsales*st.aroic)/sum(st.tsales) as roic,
      sum(st.tsales*st.ape)/sum(st.tsales) as pe,
      sum(st.tsales*st.aevebitda)/sum(st.tsales) as evebitda,
      sum(st.tsales*st.apayout)/sum(st.tsales) as payout
    from (
    select 
      d.syear,
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
    where d.sales>0
     and (I_region='' or I_region='Global' or c.region=I_region) 
     and (I_year is null or d.syear=I_year)
    group by d.syear, d.sic
    ) as st
    group by st.syear;
end$$

create procedure summary_by_sics_by_years(I_funct VARCHAR(20), I_region varchar(255))
begin
    CASE I_funct
    WHEN 'stability' THEN call get_stability_by_years(I_region);  
    WHEN 'tsales' THEN call get_sics_totals(I_region);
    WHEN 'top3' THEN call get_topN_by_years(3, NULL, I_region);
    WHEN 'top5' THEN call get_topN_by_years(5, NULL, I_region);
    WHEN 'sales_growth' THEN call get_calc_by_years(NULL, I_region);
    WHEN 'roic' THEN call get_calc_by_years(NULL, I_region);
    WHEN 'pe' THEN call get_calc_by_years(NULL, I_region);
    WHEN 'evebitda' THEN call get_calc_by_years(NULL, I_region);
    WHEN 'payout' THEN call get_calc_by_years(NULL, I_region);
    ELSE
      BEGIN
        select -1 as syear, null as v;
      END;
    END CASE;    
end $$

create procedure select_single_sic(I_sic integer)
begin
    DROP TABLE IF EXISTS tmp_selected_sics;
    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_selected_sics (sic integer NOT NULL);
    insert into tmp_selected_sics values (I_sic);
end $$

create procedure select_sics_by_theme_range(I_max_year integer,
 I_theme_id integer, I_theme_min integer, I_theme_max integer,
 I_region varchar(255))

begin
    DROP TABLE IF EXISTS tmp_selected_sics;
    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_selected_sics (sic integer NOT NULL);

    IF I_region='' or I_region='Global' THEN
        insert into tmp_selected_sics
        select distinct id
        from sales_sic 
        where CSV_DOUBLE(exposure,I_theme_id)  between I_theme_min and I_theme_max
        and id<>9999
        order by id;
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
end $$

create procedure select_companies_by_theme_range(I_max_year integer,
 I_theme_id integer, I_theme_min double, I_theme_max double,
 I_region varchar(255))
begin
    DROP TABLE IF EXISTS tmp_selected_cids;
    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_selected_cids (cid varchar(16) NOT NULL);
      
    insert into tmp_selected_cids
    select 
      c.cid
    from 
    ( select syear, cid, sic
      from sales_divdetails 
      where sales>0  
      and (I_max_year is NULL or I_max_year=syear)
      group by syear, cid, sic 
    ) as d
    join sales_companies c on  d.cid = c.cid
    join sales_sic s on d.sic=s.id
    join sales_sic_companies_totals t on d.cid=t.cid and d.sic=t.sic
    where (I_region='' or I_region='Global' or c.region=I_region) 
    -- and s.id<>9999
    group by c.cid
    having sum(CSV_DOUBLE(s.exposure,I_theme_id)*t.psale/100) between I_theme_min and I_theme_max;
end $$

create procedure select_sics_by_themes(I_theme_id integer, I_theme_min integer, I_theme_max integer)
begin
    DROP TABLE IF EXISTS tmp_selected_sics;
    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_selected_sics (sic integer NOT NULL);
      insert into tmp_selected_sics
        select distinct s.id
          from sales_divdetails d
        join sales_sic s on d.sic=s.id
        where d.sales>0 
        and CSV_DOUBLE(s.exposure,I_theme_id) between I_theme_min and I_theme_max 
        and s.id<>9999
        order by s.id;
end $$

-- OUT: tmp_sales_growth_by_sic_year
create procedure sales_growth_by_year(I_max_year integer, I_region varchar(255))
begin
     DROP TABLE IF EXISTS tmp_vsum_by_cid_sic_year;
     
     CREATE TEMPORARY TABLE
IF NOT EXISTS tmp_vsum_by_cid_sic_year 
(syear integer not null, cid varchar(16) NOT NULL, sic integer NOT NULL,
v double not null);
     
     insert into tmp_vsum_by_cid_sic_year
select 
    d.syear,
    d.cid,
    d.sic,
    sum(d.sales)
from sales_divdetails d
   join sales_companies c on  d.cid = c.cid
   join tmp_selected_sics ss on d.sic=ss.sic
where  d.sales is not null 
   and d.syear in (I_max_year-1, I_max_year)
   and (I_region='' or I_region='Global' or c.region=I_region)
group by d.syear, d.cid, d.sic
having sum(d.sales)>0;

    DROP TABLE IF EXISTS tmp_sales_growth_by_sic_year;
    
    CREATE TEMPORARY TABLE
    IF NOT EXISTS tmp_sales_growth_by_sic_year
    (syear integer not null, sic integer NOT NULL,
    v double not null);

    insert into tmp_sales_growth_by_sic_year
select 
    r2.syear,
    r2.sic,
    sum(gv*r2.v)/sum(r2.v) as v
from
(   select 
      r.syear,
      r.cid,
      r.sic,
      r.v,
      100*(r.v/t.v-1) as gv
    from
    (select 
        d.syear,
        d.cid,
        d.sic,
        sum(d.sales) as v    
    from sales_divdetails d
       join sales_companies c on  d.cid = c.cid
       join tmp_selected_sics ss on d.sic=ss.sic
    where  d.sales is not null
        and d.syear in (I_max_year-1, I_max_year)
        and (I_region='' or I_region='Global' or c.region=I_region) 
    group by d.syear, d.cid, d.sic
    having sum(d.sales)>0
    ) as r
    join tmp_vsum_by_cid_sic_year t 
        on t.syear=r.syear-1 
        and t.cid=r.cid
        and t.sic=r.sic
) as r2
group by 1,2
order by 2, 1 desc;

end $$

/*
call selected_sics_by...
before using

set @year = 2015;
-- Theme A
set @theme_id = 1;
set @theme_min = 2;
set @theme_max = 2;
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
    DECLARE L_sales_growth DOUBLE;
  
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
    call get_topN_by_sic_years(3,I_max_year,I_region);
    select sum(p.proc*t.tsum)/sum(t.tsum) as v        
    from tmp_totalN_by_sic_years p
    join tmp_total_sic_tsales_by_years t
        on p.syear=t.syear and p.sic=t.sic
    group by p.syear into L_top3sum;

     -- select top 5
   call get_topN_by_sic_years(5,I_max_year,I_region);
    select sum(p.proc*t.tsum)/sum(t.tsum) as v        
    from tmp_totalN_by_sic_years p
    join tmp_total_sic_tsales_by_years t
        on p.syear=t.syear and p.sic=t.sic
    group by p.syear into L_top5sum;
     
    -- Sales growth 
    call sales_growth_by_year(I_max_year,I_region);
    select sum(p.v*t.tsum)/sum(t.tsum) as v        
    from tmp_sales_growth_by_sic_year p
    join tmp_total_sic_tsales_by_years t
        on p.syear=t.syear and p.sic=t.sic
    group by p.syear into L_sales_growth;
     
     
    -- select stability
    call get_sics_stabilities(I_max_year, I_region);
    --call get_stability(I_max_year, I_region, L_stability);

    -- select theme values
    select 
      I_name as name,
      L_top3sum as top3sum,
      L_top5sum as top5sum,
      sum(st.tsales*st.stability)/sum(st.tsales) as stability,
      sum(st.tsales) as tsales,
      L_sales_growth as asales_growth,
      sum(st.tsales*st.aroic)/sum(st.tsales) as aroic,
      sum(st.tsales*st.ape)/sum(st.tsales) as ape,
      sum(st.tsales*st.aevebitda)/sum(st.tsales) as aevebitda,
      sum(st.tsales*st.apayout)/sum(st.tsales) as apayout,
      L_previewed as previewed
    from (
    select 
      p.sic,
      st.stability,
      sum(d.sales) as tsales,
      sum(c.roic*p.psale*t.sales)/sum(p.psale*t.sales) as aroic,
      sum(c.pe*p.psale*t.sales)/sum(p.psale*t.sales) as ape,
      sum(c.evebitda*p.psale*t.sales)/sum(p.psale*t.sales) as aevebitda,
      sum(c.payout*p.psale*t.sales)/sum(p.psale*t.sales) as apayout
    from sales_divdetails d
    join sales_companies c on  d.cid = c.cid
    join tmp_selected_sics ss on d.sic=ss.sic
    join sales_sic_companies_totals p on d.cid=p.cid and d.sic=p.sic
    join sales_companies_totals t on d.cid=t.cid
    join tmp_stabilities st on p.sic=st.sic    
    where d.syear=I_max_year and d.sales>0
    and (I_region='' or I_region='Global' or c.region=I_region)
    group by d.sic, p.sic, st.stability
    ) as st;

end $$

create procedure selectCustomSics()
begin
   DROP TABLE IF EXISTS tmp_selected_sics;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_selected_sics (sic integer NOT NULL);
   insert into tmp_selected_sics values (1311),(2821),(2911),(4612),(4922),(5331),(9999);
end $$

delimiter ;

call update_sales_totals;
