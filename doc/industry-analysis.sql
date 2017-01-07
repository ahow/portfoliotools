select max(syear), min(syear) from sales_divdetails into @maxyear, @minyear;

CREATE TEMPORARY TABLE tmp_cid_sales (cid varchar(16) NOT NULL, tsales double, primary key (cid)) ENGINE=MEMORY;

insert into tmp_cid_sales
select d.cid, sum(d.sales)
from sales_divdetails d
where d.syear=@maxyear
group by 1

select d.cid, sum(d.sales)
from sales_divdetails d
where d.syear=@maxyear
group by 1


CREATE TEMPORARY TABLE tmp_cid_sic_proc (cid varchar(16) not null, sic integer NOT NULL, proc double, primary key (cid,sic)) ENGINE=MEMORY;

insert into tmp_cid_sic_proc
select d.cid, d.sic, sum(d.sales)/t.tsales
from sales_divdetails d
join tmp_cid_sales t on d.cid=t.cid
where d.syear=@maxyear
group by 1,2

select 
  s.name,
  t.sic,
  sum(c.sales_growth*t.proc)/sum(t.proc) as psales_growth,
  sum(c.roic*t.proc)/sum(t.proc) as proic,
  sum(c.pe*t.proc)/sum(t.proc) as ppe,
  sum(c.evebitda*t.proc)/sum(t.proc) as pevebitda,
  sum(c.payout*t.proc)/sum(t.proc) as ppayout,
  sum(c.reviewed*t.proc)/sum(t.proc) as prewieved
from sales_companies c
join tmp_cid_sic_proc t on c.cid = t.cid
join sales_sic s on t.sic=s.id
group by 1,2
limit 3;



