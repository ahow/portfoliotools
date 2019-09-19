GOST engine already loaded
-- boss[boss] @ localhost []   thread: 1154
set names utf8;

-- boss[boss] @ localhost []   thread: 1154
select * from mc_sessions where session='0000000000000000fad26a6a435821166764eb7db991c75b';

-- boss[boss] @ localhost []   thread: 1154
select id, name, lastname,firstname,auth_module from mc_users where id=1;

-- boss[boss] @ localhost []   thread: 1154
select g.grname from mc_usergroups ug join mc_users u on ug.user_id=u.id join mc_groups g on ug.group_id=g.id where u.name = 'admin';

-- boss[boss] @ localhost []   thread: 1154
select max(d.syear) as maxyear
from sales_divdetails d;

-- boss[boss] @ localhost []   thread: 1154
select max(d.syear) as maxyear
from sales_divdetails d;

-- boss[boss] @ localhost []   thread: 1154
select headers from sales_exposure;

-- boss[boss] @ localhost []   thread: 1154
set @year='2015';

-- boss[boss] @ localhost []   thread: 1154
CREATE TEMPORARY TABLE tmp_subsector_total (subsector  varchar(100),  total double, index(subsector)) ENGINE=MEMORY;

-- boss[boss] @ localhost []   thread: 1154
insert into tmp_subsector_total
select c.subsector, sum(d.sales)
from sales_companies c
join sales_divdetails d on c.cid = d.cid and d.syear=@year
group by 1;

-- boss[boss] @ localhost []   thread: 1154
CREATE TEMPORARY TABLE tmp_subsector_values (subsector  varchar(100), p1 double, p2 double, p3 double, p4 double, index(subsector)) ENGINE=MEMORY;

-- boss[boss] @ localhost []   thread: 1154
insert into tmp_subsector_values
select 
    c.subsector, sum(d.sales*CSV_DOUBLE(s.exposure,1))/t.total as p1
, sum(d.sales*CSV_DOUBLE(s.exposure,2))/t.total as p2
, sum(d.sales*CSV_DOUBLE(s.exposure,3))/t.total as p3
, sum(d.sales*CSV_DOUBLE(s.exposure,4))/t.total as p4
from sales_companies c
join sales_divdetails d on c.cid = d.cid and d.syear=@year
join sales_sic s on d.sic=s.id
join tmp_subsector_total t on c.subsector=t.subsector
group by 1, t.total;

-- boss[boss] @ localhost []   thread: 1154
set @pf='2';

-- boss[boss] @ localhost []   thread: 1154
CREATE TEMPORARY TABLE tmp_cids (cid varchar(16) NOT NULL, isin varchar(32),
 reviewed boolean, primary key (cid), index(isin))  ENGINE=MEMORY;

-- boss[boss] @ localhost []   thread: 1154
insert into tmp_cids
select 
    c.cid, c.isin, false
from sales_companies c
join sales_divdetails d on c.cid = d.cid and d.syear=@year
join sales_portfolio_data p on c.isin = p.isin and p.portfolio_id=@pf
where not c.reviewed
group by 1,2;

-- boss[boss] @ localhost []   thread: 1154
insert into tmp_cids
select 
    c.cid, c.isin, true
from sales_companies c
join sales_divdetails d on c.cid = d.cid and d.syear=@year
join sales_portfolio_data p on c.isin = p.isin and p.portfolio_id=@pf
where c.reviewed
group by 1,2;

-- boss[boss] @ localhost []   thread: 1154
select sum(p.val)
from tmp_cids t
join sales_portfolio_data p on t.isin = p.isin and portfolio_id=@pf
into @pfsum;

-- boss[boss] @ localhost []   thread: 1154
CREATE TEMPORARY TABLE tmp_fin_portfolio_values ( isin varchar(32),reviewed boolean, adjucted double , p1 double , p2 double , p3 double , p4 double , index(isin)) ENGINE=MEMORY;

-- boss[boss] @ localhost []   thread: 1154
insert into tmp_fin_portfolio_values
select t.isin, c.reviewed, p.val/@pfsum as adjucted , sv.p1 , sv.p2 , sv.p3 , sv.p4 from tmp_cids t
join sales_companies c on t.cid=c.cid
join tmp_subsector_values sv on c.subsector = sv.subsector
join sales_portfolio_data p on t.isin = p.isin and portfolio_id=@pf;

-- boss[boss] @ localhost []   thread: 1154
select c.name, c.subsector, c.cid, t.* from tmp_fin_portfolio_values t join sales_companies c on t.isin=c.isin;

-- boss[boss] @ localhost []   thread: 1154
CREATE TEMPORARY TABLE tmp_companie_total_sales ( cid varchar(16), total double, index(cid)) ENGINE=MEMORY;

-- boss[boss] @ localhost []   thread: 1154
insert into tmp_companie_total_sales
select 
  d.cid, 
  sum(d.sales) as total
from tmp_cids t
join sales_divdetails d on d.cid=t.cid and t.reviewed and d.syear=@year and d.sales is not null
group by 1;

-- boss[boss] @ localhost []   thread: 1154
insert into tmp_fin_portfolio_values
select t.isin, true as reviewed, p.val/@pfsum as adjucted,sum( d.sales*CSV_DOUBLE(s.exposure,1) ) / ts.total as t1 ,sum( d.sales*CSV_DOUBLE(s.exposure,2) ) / ts.total as t2 ,sum( d.sales*CSV_DOUBLE(s.exposure,3) ) / ts.total as t3 ,sum( d.sales*CSV_DOUBLE(s.exposure,4) ) / ts.total as t4 from tmp_cids t
join sales_divdetails d on t.cid=d.cid
join sales_sic s on d.sic=s.id
join tmp_companie_total_sales ts on t.cid = ts.cid
join sales_portfolio_data p on t.isin = p.isin and portfolio_id=@pf
where t.reviewed and d.syear=@year and d.sales is not null
group by 1,2,3, ts.total;

-- boss[boss] @ localhost []   thread: 1154
select sum(t.p1*t.adjucted) as s1, sum(t.p2*t.adjucted) as s2, sum(t.p3*t.adjucted) as s3, sum(t.p4*t.adjucted) as s4, count(*) as count
from tmp_fin_portfolio_values t;

-- boss[boss] @ localhost []   thread: 1154
drop table tmp_cids;

-- boss[boss] @ localhost []   thread: 1154
drop table tmp_companie_total_sales;

-- boss[boss] @ localhost []   thread: 1154
drop table tmp_fin_portfolio_values;

-- boss[boss] @ localhost []   thread: 1154
set @pf='2';

-- boss[boss] @ localhost []   thread: 1154
CREATE TEMPORARY TABLE tmp_cids (cid varchar(16) NOT NULL, isin varchar(32),
 reviewed boolean, primary key (cid), index(isin))  ENGINE=MEMORY;

-- boss[boss] @ localhost []   thread: 1154
insert into tmp_cids
select 
    c.cid, c.isin, false
from sales_companies c
join sales_divdetails d on c.cid = d.cid and d.syear=@year
join sales_portfolio_data p on c.isin = p.isin and p.portfolio_id=@pf
where not c.reviewed
group by 1,2;

-- boss[boss] @ localhost []   thread: 1154
insert into tmp_cids
select 
    c.cid, c.isin, true
from sales_companies c
join sales_divdetails d on c.cid = d.cid and d.syear=@year
join sales_portfolio_data p on c.isin = p.isin and p.portfolio_id=@pf
where c.reviewed
group by 1,2;

-- boss[boss] @ localhost []   thread: 1154
select sum(p.val)
from tmp_cids t
join sales_portfolio_data p on t.isin = p.isin and portfolio_id=@pf
into @pfsum;

-- boss[boss] @ localhost []   thread: 1154
CREATE TEMPORARY TABLE tmp_fin_portfolio_values ( isin varchar(32),reviewed boolean, adjucted double , p1 double , p2 double , p3 double , p4 double , index(isin)) ENGINE=MEMORY;

-- boss[boss] @ localhost []   thread: 1154
insert into tmp_fin_portfolio_values
select t.isin, c.reviewed, p.val/@pfsum as adjucted , sv.p1 , sv.p2 , sv.p3 , sv.p4 from tmp_cids t
join sales_companies c on t.cid=c.cid
join tmp_subsector_values sv on c.subsector = sv.subsector
join sales_portfolio_data p on t.isin = p.isin and portfolio_id=@pf;

-- boss[boss] @ localhost []   thread: 1154
CREATE TEMPORARY TABLE tmp_companie_total_sales ( cid varchar(16), total double, index(cid)) ENGINE=MEMORY;

-- boss[boss] @ localhost []   thread: 1154
insert into tmp_companie_total_sales
select 
  d.cid, 
  sum(d.sales) as total
from tmp_cids t
join sales_divdetails d on d.cid=t.cid and t.reviewed and d.syear=@year and d.sales is not null
group by 1;

-- boss[boss] @ localhost []   thread: 1154
insert into tmp_fin_portfolio_values
select t.isin, true as reviewed, p.val/@pfsum as adjucted,sum( d.sales*CSV_DOUBLE(s.exposure,1) ) / ts.total as t1 ,sum( d.sales*CSV_DOUBLE(s.exposure,2) ) / ts.total as t2 ,sum( d.sales*CSV_DOUBLE(s.exposure,3) ) / ts.total as t3 ,sum( d.sales*CSV_DOUBLE(s.exposure,4) ) / ts.total as t4 from tmp_cids t
join sales_divdetails d on t.cid=d.cid
join sales_sic s on d.sic=s.id
join tmp_companie_total_sales ts on t.cid = ts.cid
join sales_portfolio_data p on t.isin = p.isin and portfolio_id=@pf
where t.reviewed and d.syear=@year and d.sales is not null
group by 1,2,3, ts.total;

-- boss[boss] @ localhost []   thread: 1154
select sum(t.p1*t.adjucted) as s1, sum(t.p2*t.adjucted) as s2, sum(t.p3*t.adjucted) as s3, sum(t.p4*t.adjucted) as s4, count(*) as count
from tmp_fin_portfolio_values t;

-- boss[boss] @ localhost []   thread: 1154
drop table tmp_cids;

-- boss[boss] @ localhost []   thread: 1154
drop table tmp_companie_total_sales;

-- boss[boss] @ localhost []   thread: 1154
drop table tmp_fin_portfolio_values;

-- boss[boss] @ localhost []   thread: 1154
select portfolio from sales_portfolio where id='2';

-- boss[boss] @ localhost []   thread: 1154
select portfolio from sales_portfolio where id='2';

-- root[root] @ localhost []   thread: 1149
select * from mysql.general_log limit 10;

-- root[root] @ localhost []   thread: 1149
select * from mysql.general_log where command_type='Query';

-- root[root] @ localhost []   thread: 1149
select * from mysql.general_log where command_type='Query';

-- root[root] @ localhost []   thread: 1149
select * from mysql.general_log where command_type='Query';

-- root[root] @ localhost []   thread: 1149
delete from  mysql.general_log;

-- root[root] @ localhost []   thread: 1149
delete from mysql.general_log;

-- root[root] @ localhost []   thread: 1149
delete from mysql.general_log;

-- root[root] @ localhost []   thread: 1149
select * from mysql.general_log where command_type='Query';

-- root[root] @ localhost []   thread: 1149
select * from mysql.general_log where command_type='Query' limit 1;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1141
SELECT
  T.TABLE_NAME AS tableName,
  T.TABLE_SCHEMA AS tableSchema,
  T.TABLE_CATALOG AS tableCatalog,
  (
    CASE
      WHEN T.TABLE_TYPE = 'VIEW' THEN 1
      ELSE 0
    END
  ) AS isView,
  T.TABLE_SCHEMA AS dbName,
  COUNT(1) AS numberOfColumns,
  CONCAT(
    T.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    T.TABLE_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.TABLES AS T
  LEFT JOIN INFORMATION_SCHEMA.COLUMNS AS C ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND (C.TABLE_CATALOG IS NULL OR C.TABLE_CATALOG = T.TABLE_CATALOG)
WHERE
  T.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  T.TABLE_NAME,
  T.TABLE_SCHEMA,
  T.TABLE_CATALOG,
  T.TABLE_TYPE
ORDER BY
  T.TABLE_NAME;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1142
SELECT
  C.TABLE_NAME AS tableName,
  C.COLUMN_NAME AS columnName,
  C.DATA_TYPE AS type,
  C.CHARACTER_MAXIMUM_LENGTH AS size,
  C.TABLE_SCHEMA as tableSchema,
  C.TABLE_CATALOG AS tableCatalog,
  C.TABLE_SCHEMA as tableDatabase,
  C.COLUMN_DEFAULT as defaultValue,
  C.IS_NULLABLE as isNullable,
  C.ORDINAL_POSITION,
  (
    CASE
      WHEN C.COLUMN_KEY = 'PRI' THEN TRUE
      ELSE FALSE
    END
  ) as isPk,
  (
    CASE
      WHEN KCU.REFERENCED_COLUMN_NAME IS NULL THEN FALSE
      ELSE TRUE
    END
  ) as isFk,
  CONCAT(
    C.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    C.TABLE_name,
    '/-##-/',
    C.COLUMN_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.COLUMNS AS C
  LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU ON (
    C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.TABLE_NAME = KCU.TABLE_NAME
    AND C.TABLE_SCHEMA = KCU.TABLE_SCHEMA
    AND C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.COLUMN_NAME = KCU.COLUMN_NAME
  )
  JOIN INFORMATION_SCHEMA.TABLES AS T ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND C.TABLE_CATALOG = T.TABLE_CATALOG
WHERE
  C.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
ORDER BY
  C.TABLE_NAME,
  C.ORDINAL_POSITION;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1147
SELECT
  f.specific_name AS name,
  f.routine_schema AS dbschema,
  f.routine_schema AS dbname,
  concat(
    case
      WHEN f.routine_schema REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_schema, '`')
      ELSE f.routine_schema
    end,
    '.',
    case
      WHEN f.routine_name REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_name, '`')
      ELSE f.routine_name
    end
  ) as signature,
  GROUP_CONCAT(p.data_type) as args,
  f.data_type AS resultType,
  CONCAT(
    f.routine_schema,
    '/-##-/',
    'functions',
    '/-##-/',
    f.specific_name
  ) AS tree,
  f.routine_definition AS source
FROM
  information_schema.routines AS f
  LEFT JOIN information_schema.parameters AS p ON (
    f.specific_name = p.specific_name
    AND f.routine_schema = p.specific_schema
    AND f.routine_catalog = p.specific_catalog
  )
WHERE
  f.routine_schema NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  f.specific_name,
  f.routine_schema,
  f.routine_name,
  f.data_type,
  f.routine_definition
ORDER BY
  f.specific_name;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1146
SELECT
  T.TABLE_NAME AS tableName,
  T.TABLE_SCHEMA AS tableSchema,
  T.TABLE_CATALOG AS tableCatalog,
  (
    CASE
      WHEN T.TABLE_TYPE = 'VIEW' THEN 1
      ELSE 0
    END
  ) AS isView,
  T.TABLE_SCHEMA AS dbName,
  COUNT(1) AS numberOfColumns,
  CONCAT(
    T.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    T.TABLE_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.TABLES AS T
  LEFT JOIN INFORMATION_SCHEMA.COLUMNS AS C ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND (C.TABLE_CATALOG IS NULL OR C.TABLE_CATALOG = T.TABLE_CATALOG)
WHERE
  T.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  T.TABLE_NAME,
  T.TABLE_SCHEMA,
  T.TABLE_CATALOG,
  T.TABLE_TYPE
ORDER BY
  T.TABLE_NAME;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1144
SELECT
  C.TABLE_NAME AS tableName,
  C.COLUMN_NAME AS columnName,
  C.DATA_TYPE AS type,
  C.CHARACTER_MAXIMUM_LENGTH AS size,
  C.TABLE_SCHEMA as tableSchema,
  C.TABLE_CATALOG AS tableCatalog,
  C.TABLE_SCHEMA as tableDatabase,
  C.COLUMN_DEFAULT as defaultValue,
  C.IS_NULLABLE as isNullable,
  C.ORDINAL_POSITION,
  (
    CASE
      WHEN C.COLUMN_KEY = 'PRI' THEN TRUE
      ELSE FALSE
    END
  ) as isPk,
  (
    CASE
      WHEN KCU.REFERENCED_COLUMN_NAME IS NULL THEN FALSE
      ELSE TRUE
    END
  ) as isFk,
  CONCAT(
    C.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    C.TABLE_name,
    '/-##-/',
    C.COLUMN_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.COLUMNS AS C
  LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU ON (
    C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.TABLE_NAME = KCU.TABLE_NAME
    AND C.TABLE_SCHEMA = KCU.TABLE_SCHEMA
    AND C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.COLUMN_NAME = KCU.COLUMN_NAME
  )
  JOIN INFORMATION_SCHEMA.TABLES AS T ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND C.TABLE_CATALOG = T.TABLE_CATALOG
WHERE
  C.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
ORDER BY
  C.TABLE_NAME,
  C.ORDINAL_POSITION;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1148
SELECT
  f.specific_name AS name,
  f.routine_schema AS dbschema,
  f.routine_schema AS dbname,
  concat(
    case
      WHEN f.routine_schema REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_schema, '`')
      ELSE f.routine_schema
    end,
    '.',
    case
      WHEN f.routine_name REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_name, '`')
      ELSE f.routine_name
    end
  ) as signature,
  GROUP_CONCAT(p.data_type) as args,
  f.data_type AS resultType,
  CONCAT(
    f.routine_schema,
    '/-##-/',
    'functions',
    '/-##-/',
    f.specific_name
  ) AS tree,
  f.routine_definition AS source
FROM
  information_schema.routines AS f
  LEFT JOIN information_schema.parameters AS p ON (
    f.specific_name = p.specific_name
    AND f.routine_schema = p.specific_schema
    AND f.routine_catalog = p.specific_catalog
  )
WHERE
  f.routine_schema NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  f.specific_name,
  f.routine_schema,
  f.routine_name,
  f.data_type,
  f.routine_definition
ORDER BY
  f.specific_name;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1143
SELECT
  T.TABLE_NAME AS tableName,
  T.TABLE_SCHEMA AS tableSchema,
  T.TABLE_CATALOG AS tableCatalog,
  (
    CASE
      WHEN T.TABLE_TYPE = 'VIEW' THEN 1
      ELSE 0
    END
  ) AS isView,
  T.TABLE_SCHEMA AS dbName,
  COUNT(1) AS numberOfColumns,
  CONCAT(
    T.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    T.TABLE_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.TABLES AS T
  LEFT JOIN INFORMATION_SCHEMA.COLUMNS AS C ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND (C.TABLE_CATALOG IS NULL OR C.TABLE_CATALOG = T.TABLE_CATALOG)
WHERE
  T.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  T.TABLE_NAME,
  T.TABLE_SCHEMA,
  T.TABLE_CATALOG,
  T.TABLE_TYPE
ORDER BY
  T.TABLE_NAME;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1147
SELECT
  f.specific_name AS name,
  f.routine_schema AS dbschema,
  f.routine_schema AS dbname,
  concat(
    case
      WHEN f.routine_schema REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_schema, '`')
      ELSE f.routine_schema
    end,
    '.',
    case
      WHEN f.routine_name REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_name, '`')
      ELSE f.routine_name
    end
  ) as signature,
  GROUP_CONCAT(p.data_type) as args,
  f.data_type AS resultType,
  CONCAT(
    f.routine_schema,
    '/-##-/',
    'functions',
    '/-##-/',
    f.specific_name
  ) AS tree,
  f.routine_definition AS source
FROM
  information_schema.routines AS f
  LEFT JOIN information_schema.parameters AS p ON (
    f.specific_name = p.specific_name
    AND f.routine_schema = p.specific_schema
    AND f.routine_catalog = p.specific_catalog
  )
WHERE
  f.routine_schema NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  f.specific_name,
  f.routine_schema,
  f.routine_name,
  f.data_type,
  f.routine_definition
ORDER BY
  f.specific_name;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1148
SELECT
  C.TABLE_NAME AS tableName,
  C.COLUMN_NAME AS columnName,
  C.DATA_TYPE AS type,
  C.CHARACTER_MAXIMUM_LENGTH AS size,
  C.TABLE_SCHEMA as tableSchema,
  C.TABLE_CATALOG AS tableCatalog,
  C.TABLE_SCHEMA as tableDatabase,
  C.COLUMN_DEFAULT as defaultValue,
  C.IS_NULLABLE as isNullable,
  C.ORDINAL_POSITION,
  (
    CASE
      WHEN C.COLUMN_KEY = 'PRI' THEN TRUE
      ELSE FALSE
    END
  ) as isPk,
  (
    CASE
      WHEN KCU.REFERENCED_COLUMN_NAME IS NULL THEN FALSE
      ELSE TRUE
    END
  ) as isFk,
  CONCAT(
    C.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    C.TABLE_name,
    '/-##-/',
    C.COLUMN_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.COLUMNS AS C
  LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU ON (
    C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.TABLE_NAME = KCU.TABLE_NAME
    AND C.TABLE_SCHEMA = KCU.TABLE_SCHEMA
    AND C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.COLUMN_NAME = KCU.COLUMN_NAME
  )
  JOIN INFORMATION_SCHEMA.TABLES AS T ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND C.TABLE_CATALOG = T.TABLE_CATALOG
WHERE
  C.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
ORDER BY
  C.TABLE_NAME,
  C.ORDINAL_POSITION;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1141
SELECT
  T.TABLE_NAME AS tableName,
  T.TABLE_SCHEMA AS tableSchema,
  T.TABLE_CATALOG AS tableCatalog,
  (
    CASE
      WHEN T.TABLE_TYPE = 'VIEW' THEN 1
      ELSE 0
    END
  ) AS isView,
  T.TABLE_SCHEMA AS dbName,
  COUNT(1) AS numberOfColumns,
  CONCAT(
    T.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    T.TABLE_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.TABLES AS T
  LEFT JOIN INFORMATION_SCHEMA.COLUMNS AS C ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND (C.TABLE_CATALOG IS NULL OR C.TABLE_CATALOG = T.TABLE_CATALOG)
WHERE
  T.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  T.TABLE_NAME,
  T.TABLE_SCHEMA,
  T.TABLE_CATALOG,
  T.TABLE_TYPE
ORDER BY
  T.TABLE_NAME;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1146
SELECT
  C.TABLE_NAME AS tableName,
  C.COLUMN_NAME AS columnName,
  C.DATA_TYPE AS type,
  C.CHARACTER_MAXIMUM_LENGTH AS size,
  C.TABLE_SCHEMA as tableSchema,
  C.TABLE_CATALOG AS tableCatalog,
  C.TABLE_SCHEMA as tableDatabase,
  C.COLUMN_DEFAULT as defaultValue,
  C.IS_NULLABLE as isNullable,
  C.ORDINAL_POSITION,
  (
    CASE
      WHEN C.COLUMN_KEY = 'PRI' THEN TRUE
      ELSE FALSE
    END
  ) as isPk,
  (
    CASE
      WHEN KCU.REFERENCED_COLUMN_NAME IS NULL THEN FALSE
      ELSE TRUE
    END
  ) as isFk,
  CONCAT(
    C.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    C.TABLE_name,
    '/-##-/',
    C.COLUMN_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.COLUMNS AS C
  LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU ON (
    C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.TABLE_NAME = KCU.TABLE_NAME
    AND C.TABLE_SCHEMA = KCU.TABLE_SCHEMA
    AND C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.COLUMN_NAME = KCU.COLUMN_NAME
  )
  JOIN INFORMATION_SCHEMA.TABLES AS T ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND C.TABLE_CATALOG = T.TABLE_CATALOG
WHERE
  C.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
ORDER BY
  C.TABLE_NAME,
  C.ORDINAL_POSITION;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1147
SELECT
  f.specific_name AS name,
  f.routine_schema AS dbschema,
  f.routine_schema AS dbname,
  concat(
    case
      WHEN f.routine_schema REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_schema, '`')
      ELSE f.routine_schema
    end,
    '.',
    case
      WHEN f.routine_name REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_name, '`')
      ELSE f.routine_name
    end
  ) as signature,
  GROUP_CONCAT(p.data_type) as args,
  f.data_type AS resultType,
  CONCAT(
    f.routine_schema,
    '/-##-/',
    'functions',
    '/-##-/',
    f.specific_name
  ) AS tree,
  f.routine_definition AS source
FROM
  information_schema.routines AS f
  LEFT JOIN information_schema.parameters AS p ON (
    f.specific_name = p.specific_name
    AND f.routine_schema = p.specific_schema
    AND f.routine_catalog = p.specific_catalog
  )
WHERE
  f.routine_schema NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  f.specific_name,
  f.routine_schema,
  f.routine_name,
  f.data_type,
  f.routine_definition
ORDER BY
  f.specific_name;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1142
SELECT
  C.TABLE_NAME AS tableName,
  C.COLUMN_NAME AS columnName,
  C.DATA_TYPE AS type,
  C.CHARACTER_MAXIMUM_LENGTH AS size,
  C.TABLE_SCHEMA as tableSchema,
  C.TABLE_CATALOG AS tableCatalog,
  C.TABLE_SCHEMA as tableDatabase,
  C.COLUMN_DEFAULT as defaultValue,
  C.IS_NULLABLE as isNullable,
  C.ORDINAL_POSITION,
  (
    CASE
      WHEN C.COLUMN_KEY = 'PRI' THEN TRUE
      ELSE FALSE
    END
  ) as isPk,
  (
    CASE
      WHEN KCU.REFERENCED_COLUMN_NAME IS NULL THEN FALSE
      ELSE TRUE
    END
  ) as isFk,
  CONCAT(
    C.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    C.TABLE_name,
    '/-##-/',
    C.COLUMN_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.COLUMNS AS C
  LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU ON (
    C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.TABLE_NAME = KCU.TABLE_NAME
    AND C.TABLE_SCHEMA = KCU.TABLE_SCHEMA
    AND C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.COLUMN_NAME = KCU.COLUMN_NAME
  )
  JOIN INFORMATION_SCHEMA.TABLES AS T ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND C.TABLE_CATALOG = T.TABLE_CATALOG
WHERE
  C.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
ORDER BY
  C.TABLE_NAME,
  C.ORDINAL_POSITION;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1144
SELECT
  T.TABLE_NAME AS tableName,
  T.TABLE_SCHEMA AS tableSchema,
  T.TABLE_CATALOG AS tableCatalog,
  (
    CASE
      WHEN T.TABLE_TYPE = 'VIEW' THEN 1
      ELSE 0
    END
  ) AS isView,
  T.TABLE_SCHEMA AS dbName,
  COUNT(1) AS numberOfColumns,
  CONCAT(
    T.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    T.TABLE_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.TABLES AS T
  LEFT JOIN INFORMATION_SCHEMA.COLUMNS AS C ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND (C.TABLE_CATALOG IS NULL OR C.TABLE_CATALOG = T.TABLE_CATALOG)
WHERE
  T.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  T.TABLE_NAME,
  T.TABLE_SCHEMA,
  T.TABLE_CATALOG,
  T.TABLE_TYPE
ORDER BY
  T.TABLE_NAME;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1143
SELECT
  f.specific_name AS name,
  f.routine_schema AS dbschema,
  f.routine_schema AS dbname,
  concat(
    case
      WHEN f.routine_schema REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_schema, '`')
      ELSE f.routine_schema
    end,
    '.',
    case
      WHEN f.routine_name REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_name, '`')
      ELSE f.routine_name
    end
  ) as signature,
  GROUP_CONCAT(p.data_type) as args,
  f.data_type AS resultType,
  CONCAT(
    f.routine_schema,
    '/-##-/',
    'functions',
    '/-##-/',
    f.specific_name
  ) AS tree,
  f.routine_definition AS source
FROM
  information_schema.routines AS f
  LEFT JOIN information_schema.parameters AS p ON (
    f.specific_name = p.specific_name
    AND f.routine_schema = p.specific_schema
    AND f.routine_catalog = p.specific_catalog
  )
WHERE
  f.routine_schema NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  f.specific_name,
  f.routine_schema,
  f.routine_name,
  f.data_type,
  f.routine_definition
ORDER BY
  f.specific_name;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1148
SELECT
  T.TABLE_NAME AS tableName,
  T.TABLE_SCHEMA AS tableSchema,
  T.TABLE_CATALOG AS tableCatalog,
  (
    CASE
      WHEN T.TABLE_TYPE = 'VIEW' THEN 1
      ELSE 0
    END
  ) AS isView,
  T.TABLE_SCHEMA AS dbName,
  COUNT(1) AS numberOfColumns,
  CONCAT(
    T.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    T.TABLE_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.TABLES AS T
  LEFT JOIN INFORMATION_SCHEMA.COLUMNS AS C ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND (C.TABLE_CATALOG IS NULL OR C.TABLE_CATALOG = T.TABLE_CATALOG)
WHERE
  T.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  T.TABLE_NAME,
  T.TABLE_SCHEMA,
  T.TABLE_CATALOG,
  T.TABLE_TYPE
ORDER BY
  T.TABLE_NAME;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1147
SELECT
  f.specific_name AS name,
  f.routine_schema AS dbschema,
  f.routine_schema AS dbname,
  concat(
    case
      WHEN f.routine_schema REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_schema, '`')
      ELSE f.routine_schema
    end,
    '.',
    case
      WHEN f.routine_name REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_name, '`')
      ELSE f.routine_name
    end
  ) as signature,
  GROUP_CONCAT(p.data_type) as args,
  f.data_type AS resultType,
  CONCAT(
    f.routine_schema,
    '/-##-/',
    'functions',
    '/-##-/',
    f.specific_name
  ) AS tree,
  f.routine_definition AS source
FROM
  information_schema.routines AS f
  LEFT JOIN information_schema.parameters AS p ON (
    f.specific_name = p.specific_name
    AND f.routine_schema = p.specific_schema
    AND f.routine_catalog = p.specific_catalog
  )
WHERE
  f.routine_schema NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  f.specific_name,
  f.routine_schema,
  f.routine_name,
  f.data_type,
  f.routine_definition
ORDER BY
  f.specific_name;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1143
SELECT
  C.TABLE_NAME AS tableName,
  C.COLUMN_NAME AS columnName,
  C.DATA_TYPE AS type,
  C.CHARACTER_MAXIMUM_LENGTH AS size,
  C.TABLE_SCHEMA as tableSchema,
  C.TABLE_CATALOG AS tableCatalog,
  C.TABLE_SCHEMA as tableDatabase,
  C.COLUMN_DEFAULT as defaultValue,
  C.IS_NULLABLE as isNullable,
  C.ORDINAL_POSITION,
  (
    CASE
      WHEN C.COLUMN_KEY = 'PRI' THEN TRUE
      ELSE FALSE
    END
  ) as isPk,
  (
    CASE
      WHEN KCU.REFERENCED_COLUMN_NAME IS NULL THEN FALSE
      ELSE TRUE
    END
  ) as isFk,
  CONCAT(
    C.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    C.TABLE_name,
    '/-##-/',
    C.COLUMN_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.COLUMNS AS C
  LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU ON (
    C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.TABLE_NAME = KCU.TABLE_NAME
    AND C.TABLE_SCHEMA = KCU.TABLE_SCHEMA
    AND C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.COLUMN_NAME = KCU.COLUMN_NAME
  )
  JOIN INFORMATION_SCHEMA.TABLES AS T ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND C.TABLE_CATALOG = T.TABLE_CATALOG
WHERE
  C.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
ORDER BY
  C.TABLE_NAME,
  C.ORDINAL_POSITION;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1141
SELECT
  T.TABLE_NAME AS tableName,
  T.TABLE_SCHEMA AS tableSchema,
  T.TABLE_CATALOG AS tableCatalog,
  (
    CASE
      WHEN T.TABLE_TYPE = 'VIEW' THEN 1
      ELSE 0
    END
  ) AS isView,
  T.TABLE_SCHEMA AS dbName,
  COUNT(1) AS numberOfColumns,
  CONCAT(
    T.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    T.TABLE_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.TABLES AS T
  LEFT JOIN INFORMATION_SCHEMA.COLUMNS AS C ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND (C.TABLE_CATALOG IS NULL OR C.TABLE_CATALOG = T.TABLE_CATALOG)
WHERE
  T.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  T.TABLE_NAME,
  T.TABLE_SCHEMA,
  T.TABLE_CATALOG,
  T.TABLE_TYPE
ORDER BY
  T.TABLE_NAME;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1142
SELECT
  T.TABLE_NAME AS tableName,
  T.TABLE_SCHEMA AS tableSchema,
  T.TABLE_CATALOG AS tableCatalog,
  (
    CASE
      WHEN T.TABLE_TYPE = 'VIEW' THEN 1
      ELSE 0
    END
  ) AS isView,
  T.TABLE_SCHEMA AS dbName,
  COUNT(1) AS numberOfColumns,
  CONCAT(
    T.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    T.TABLE_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.TABLES AS T
  LEFT JOIN INFORMATION_SCHEMA.COLUMNS AS C ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND (C.TABLE_CATALOG IS NULL OR C.TABLE_CATALOG = T.TABLE_CATALOG)
WHERE
  T.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  T.TABLE_NAME,
  T.TABLE_SCHEMA,
  T.TABLE_CATALOG,
  T.TABLE_TYPE
ORDER BY
  T.TABLE_NAME;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1147
SELECT
  f.specific_name AS name,
  f.routine_schema AS dbschema,
  f.routine_schema AS dbname,
  concat(
    case
      WHEN f.routine_schema REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_schema, '`')
      ELSE f.routine_schema
    end,
    '.',
    case
      WHEN f.routine_name REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_name, '`')
      ELSE f.routine_name
    end
  ) as signature,
  GROUP_CONCAT(p.data_type) as args,
  f.data_type AS resultType,
  CONCAT(
    f.routine_schema,
    '/-##-/',
    'functions',
    '/-##-/',
    f.specific_name
  ) AS tree,
  f.routine_definition AS source
FROM
  information_schema.routines AS f
  LEFT JOIN information_schema.parameters AS p ON (
    f.specific_name = p.specific_name
    AND f.routine_schema = p.specific_schema
    AND f.routine_catalog = p.specific_catalog
  )
WHERE
  f.routine_schema NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  f.specific_name,
  f.routine_schema,
  f.routine_name,
  f.data_type,
  f.routine_definition
ORDER BY
  f.specific_name;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1144
SELECT
  C.TABLE_NAME AS tableName,
  C.COLUMN_NAME AS columnName,
  C.DATA_TYPE AS type,
  C.CHARACTER_MAXIMUM_LENGTH AS size,
  C.TABLE_SCHEMA as tableSchema,
  C.TABLE_CATALOG AS tableCatalog,
  C.TABLE_SCHEMA as tableDatabase,
  C.COLUMN_DEFAULT as defaultValue,
  C.IS_NULLABLE as isNullable,
  C.ORDINAL_POSITION,
  (
    CASE
      WHEN C.COLUMN_KEY = 'PRI' THEN TRUE
      ELSE FALSE
    END
  ) as isPk,
  (
    CASE
      WHEN KCU.REFERENCED_COLUMN_NAME IS NULL THEN FALSE
      ELSE TRUE
    END
  ) as isFk,
  CONCAT(
    C.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    C.TABLE_name,
    '/-##-/',
    C.COLUMN_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.COLUMNS AS C
  LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU ON (
    C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.TABLE_NAME = KCU.TABLE_NAME
    AND C.TABLE_SCHEMA = KCU.TABLE_SCHEMA
    AND C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.COLUMN_NAME = KCU.COLUMN_NAME
  )
  JOIN INFORMATION_SCHEMA.TABLES AS T ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND C.TABLE_CATALOG = T.TABLE_CATALOG
WHERE
  C.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
ORDER BY
  C.TABLE_NAME,
  C.ORDINAL_POSITION;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1146
SELECT
  C.TABLE_NAME AS tableName,
  C.COLUMN_NAME AS columnName,
  C.DATA_TYPE AS type,
  C.CHARACTER_MAXIMUM_LENGTH AS size,
  C.TABLE_SCHEMA as tableSchema,
  C.TABLE_CATALOG AS tableCatalog,
  C.TABLE_SCHEMA as tableDatabase,
  C.COLUMN_DEFAULT as defaultValue,
  C.IS_NULLABLE as isNullable,
  C.ORDINAL_POSITION,
  (
    CASE
      WHEN C.COLUMN_KEY = 'PRI' THEN TRUE
      ELSE FALSE
    END
  ) as isPk,
  (
    CASE
      WHEN KCU.REFERENCED_COLUMN_NAME IS NULL THEN FALSE
      ELSE TRUE
    END
  ) as isFk,
  CONCAT(
    C.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    C.TABLE_name,
    '/-##-/',
    C.COLUMN_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.COLUMNS AS C
  LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU ON (
    C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.TABLE_NAME = KCU.TABLE_NAME
    AND C.TABLE_SCHEMA = KCU.TABLE_SCHEMA
    AND C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.COLUMN_NAME = KCU.COLUMN_NAME
  )
  JOIN INFORMATION_SCHEMA.TABLES AS T ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND C.TABLE_CATALOG = T.TABLE_CATALOG
WHERE
  C.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
ORDER BY
  C.TABLE_NAME,
  C.ORDINAL_POSITION;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1148
SELECT
  f.specific_name AS name,
  f.routine_schema AS dbschema,
  f.routine_schema AS dbname,
  concat(
    case
      WHEN f.routine_schema REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_schema, '`')
      ELSE f.routine_schema
    end,
    '.',
    case
      WHEN f.routine_name REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_name, '`')
      ELSE f.routine_name
    end
  ) as signature,
  GROUP_CONCAT(p.data_type) as args,
  f.data_type AS resultType,
  CONCAT(
    f.routine_schema,
    '/-##-/',
    'functions',
    '/-##-/',
    f.specific_name
  ) AS tree,
  f.routine_definition AS source
FROM
  information_schema.routines AS f
  LEFT JOIN information_schema.parameters AS p ON (
    f.specific_name = p.specific_name
    AND f.routine_schema = p.specific_schema
    AND f.routine_catalog = p.specific_catalog
  )
WHERE
  f.routine_schema NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  f.specific_name,
  f.routine_schema,
  f.routine_name,
  f.data_type,
  f.routine_definition
ORDER BY
  f.specific_name;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1143
SELECT
  T.TABLE_NAME AS tableName,
  T.TABLE_SCHEMA AS tableSchema,
  T.TABLE_CATALOG AS tableCatalog,
  (
    CASE
      WHEN T.TABLE_TYPE = 'VIEW' THEN 1
      ELSE 0
    END
  ) AS isView,
  T.TABLE_SCHEMA AS dbName,
  COUNT(1) AS numberOfColumns,
  CONCAT(
    T.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    T.TABLE_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.TABLES AS T
  LEFT JOIN INFORMATION_SCHEMA.COLUMNS AS C ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND (C.TABLE_CATALOG IS NULL OR C.TABLE_CATALOG = T.TABLE_CATALOG)
WHERE
  T.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  T.TABLE_NAME,
  T.TABLE_SCHEMA,
  T.TABLE_CATALOG,
  T.TABLE_TYPE
ORDER BY
  T.TABLE_NAME;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1147
SELECT
  C.TABLE_NAME AS tableName,
  C.COLUMN_NAME AS columnName,
  C.DATA_TYPE AS type,
  C.CHARACTER_MAXIMUM_LENGTH AS size,
  C.TABLE_SCHEMA as tableSchema,
  C.TABLE_CATALOG AS tableCatalog,
  C.TABLE_SCHEMA as tableDatabase,
  C.COLUMN_DEFAULT as defaultValue,
  C.IS_NULLABLE as isNullable,
  C.ORDINAL_POSITION,
  (
    CASE
      WHEN C.COLUMN_KEY = 'PRI' THEN TRUE
      ELSE FALSE
    END
  ) as isPk,
  (
    CASE
      WHEN KCU.REFERENCED_COLUMN_NAME IS NULL THEN FALSE
      ELSE TRUE
    END
  ) as isFk,
  CONCAT(
    C.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    C.TABLE_name,
    '/-##-/',
    C.COLUMN_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.COLUMNS AS C
  LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU ON (
    C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.TABLE_NAME = KCU.TABLE_NAME
    AND C.TABLE_SCHEMA = KCU.TABLE_SCHEMA
    AND C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.COLUMN_NAME = KCU.COLUMN_NAME
  )
  JOIN INFORMATION_SCHEMA.TABLES AS T ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND C.TABLE_CATALOG = T.TABLE_CATALOG
WHERE
  C.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
ORDER BY
  C.TABLE_NAME,
  C.ORDINAL_POSITION;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1148
SELECT
  f.specific_name AS name,
  f.routine_schema AS dbschema,
  f.routine_schema AS dbname,
  concat(
    case
      WHEN f.routine_schema REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_schema, '`')
      ELSE f.routine_schema
    end,
    '.',
    case
      WHEN f.routine_name REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_name, '`')
      ELSE f.routine_name
    end
  ) as signature,
  GROUP_CONCAT(p.data_type) as args,
  f.data_type AS resultType,
  CONCAT(
    f.routine_schema,
    '/-##-/',
    'functions',
    '/-##-/',
    f.specific_name
  ) AS tree,
  f.routine_definition AS source
FROM
  information_schema.routines AS f
  LEFT JOIN information_schema.parameters AS p ON (
    f.specific_name = p.specific_name
    AND f.routine_schema = p.specific_schema
    AND f.routine_catalog = p.specific_catalog
  )
WHERE
  f.routine_schema NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  f.specific_name,
  f.routine_schema,
  f.routine_name,
  f.data_type,
  f.routine_definition
ORDER BY
  f.specific_name;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1141
SELECT
  T.TABLE_NAME AS tableName,
  T.TABLE_SCHEMA AS tableSchema,
  T.TABLE_CATALOG AS tableCatalog,
  (
    CASE
      WHEN T.TABLE_TYPE = 'VIEW' THEN 1
      ELSE 0
    END
  ) AS isView,
  T.TABLE_SCHEMA AS dbName,
  COUNT(1) AS numberOfColumns,
  CONCAT(
    T.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    T.TABLE_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.TABLES AS T
  LEFT JOIN INFORMATION_SCHEMA.COLUMNS AS C ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND (C.TABLE_CATALOG IS NULL OR C.TABLE_CATALOG = T.TABLE_CATALOG)
WHERE
  T.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  T.TABLE_NAME,
  T.TABLE_SCHEMA,
  T.TABLE_CATALOG,
  T.TABLE_TYPE
ORDER BY
  T.TABLE_NAME;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1148
SELECT
  C.TABLE_NAME AS tableName,
  C.COLUMN_NAME AS columnName,
  C.DATA_TYPE AS type,
  C.CHARACTER_MAXIMUM_LENGTH AS size,
  C.TABLE_SCHEMA as tableSchema,
  C.TABLE_CATALOG AS tableCatalog,
  C.TABLE_SCHEMA as tableDatabase,
  C.COLUMN_DEFAULT as defaultValue,
  C.IS_NULLABLE as isNullable,
  C.ORDINAL_POSITION,
  (
    CASE
      WHEN C.COLUMN_KEY = 'PRI' THEN TRUE
      ELSE FALSE
    END
  ) as isPk,
  (
    CASE
      WHEN KCU.REFERENCED_COLUMN_NAME IS NULL THEN FALSE
      ELSE TRUE
    END
  ) as isFk,
  CONCAT(
    C.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    C.TABLE_name,
    '/-##-/',
    C.COLUMN_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.COLUMNS AS C
  LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU ON (
    C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.TABLE_NAME = KCU.TABLE_NAME
    AND C.TABLE_SCHEMA = KCU.TABLE_SCHEMA
    AND C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.COLUMN_NAME = KCU.COLUMN_NAME
  )
  JOIN INFORMATION_SCHEMA.TABLES AS T ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND C.TABLE_CATALOG = T.TABLE_CATALOG
WHERE
  C.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
ORDER BY
  C.TABLE_NAME,
  C.ORDINAL_POSITION;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1142
SELECT
  f.specific_name AS name,
  f.routine_schema AS dbschema,
  f.routine_schema AS dbname,
  concat(
    case
      WHEN f.routine_schema REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_schema, '`')
      ELSE f.routine_schema
    end,
    '.',
    case
      WHEN f.routine_name REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_name, '`')
      ELSE f.routine_name
    end
  ) as signature,
  GROUP_CONCAT(p.data_type) as args,
  f.data_type AS resultType,
  CONCAT(
    f.routine_schema,
    '/-##-/',
    'functions',
    '/-##-/',
    f.specific_name
  ) AS tree,
  f.routine_definition AS source
FROM
  information_schema.routines AS f
  LEFT JOIN information_schema.parameters AS p ON (
    f.specific_name = p.specific_name
    AND f.routine_schema = p.specific_schema
    AND f.routine_catalog = p.specific_catalog
  )
WHERE
  f.routine_schema NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  f.specific_name,
  f.routine_schema,
  f.routine_name,
  f.data_type,
  f.routine_definition
ORDER BY
  f.specific_name;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1146
SELECT
  C.TABLE_NAME AS tableName,
  C.COLUMN_NAME AS columnName,
  C.DATA_TYPE AS type,
  C.CHARACTER_MAXIMUM_LENGTH AS size,
  C.TABLE_SCHEMA as tableSchema,
  C.TABLE_CATALOG AS tableCatalog,
  C.TABLE_SCHEMA as tableDatabase,
  C.COLUMN_DEFAULT as defaultValue,
  C.IS_NULLABLE as isNullable,
  C.ORDINAL_POSITION,
  (
    CASE
      WHEN C.COLUMN_KEY = 'PRI' THEN TRUE
      ELSE FALSE
    END
  ) as isPk,
  (
    CASE
      WHEN KCU.REFERENCED_COLUMN_NAME IS NULL THEN FALSE
      ELSE TRUE
    END
  ) as isFk,
  CONCAT(
    C.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    C.TABLE_name,
    '/-##-/',
    C.COLUMN_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.COLUMNS AS C
  LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU ON (
    C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.TABLE_NAME = KCU.TABLE_NAME
    AND C.TABLE_SCHEMA = KCU.TABLE_SCHEMA
    AND C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.COLUMN_NAME = KCU.COLUMN_NAME
  )
  JOIN INFORMATION_SCHEMA.TABLES AS T ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND C.TABLE_CATALOG = T.TABLE_CATALOG
WHERE
  C.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
ORDER BY
  C.TABLE_NAME,
  C.ORDINAL_POSITION;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1147
SELECT
  T.TABLE_NAME AS tableName,
  T.TABLE_SCHEMA AS tableSchema,
  T.TABLE_CATALOG AS tableCatalog,
  (
    CASE
      WHEN T.TABLE_TYPE = 'VIEW' THEN 1
      ELSE 0
    END
  ) AS isView,
  T.TABLE_SCHEMA AS dbName,
  COUNT(1) AS numberOfColumns,
  CONCAT(
    T.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    T.TABLE_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.TABLES AS T
  LEFT JOIN INFORMATION_SCHEMA.COLUMNS AS C ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND (C.TABLE_CATALOG IS NULL OR C.TABLE_CATALOG = T.TABLE_CATALOG)
WHERE
  T.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  T.TABLE_NAME,
  T.TABLE_SCHEMA,
  T.TABLE_CATALOG,
  T.TABLE_TYPE
ORDER BY
  T.TABLE_NAME;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1144
SELECT
  T.TABLE_NAME AS tableName,
  T.TABLE_SCHEMA AS tableSchema,
  T.TABLE_CATALOG AS tableCatalog,
  (
    CASE
      WHEN T.TABLE_TYPE = 'VIEW' THEN 1
      ELSE 0
    END
  ) AS isView,
  T.TABLE_SCHEMA AS dbName,
  COUNT(1) AS numberOfColumns,
  CONCAT(
    T.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    T.TABLE_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.TABLES AS T
  LEFT JOIN INFORMATION_SCHEMA.COLUMNS AS C ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND (C.TABLE_CATALOG IS NULL OR C.TABLE_CATALOG = T.TABLE_CATALOG)
WHERE
  T.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  T.TABLE_NAME,
  T.TABLE_SCHEMA,
  T.TABLE_CATALOG,
  T.TABLE_TYPE
ORDER BY
  T.TABLE_NAME;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1143
SELECT
  f.specific_name AS name,
  f.routine_schema AS dbschema,
  f.routine_schema AS dbname,
  concat(
    case
      WHEN f.routine_schema REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_schema, '`')
      ELSE f.routine_schema
    end,
    '.',
    case
      WHEN f.routine_name REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_name, '`')
      ELSE f.routine_name
    end
  ) as signature,
  GROUP_CONCAT(p.data_type) as args,
  f.data_type AS resultType,
  CONCAT(
    f.routine_schema,
    '/-##-/',
    'functions',
    '/-##-/',
    f.specific_name
  ) AS tree,
  f.routine_definition AS source
FROM
  information_schema.routines AS f
  LEFT JOIN information_schema.parameters AS p ON (
    f.specific_name = p.specific_name
    AND f.routine_schema = p.specific_schema
    AND f.routine_catalog = p.specific_catalog
  )
WHERE
  f.routine_schema NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  f.specific_name,
  f.routine_schema,
  f.routine_name,
  f.data_type,
  f.routine_definition
ORDER BY
  f.specific_name;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1142
SELECT
  C.TABLE_NAME AS tableName,
  C.COLUMN_NAME AS columnName,
  C.DATA_TYPE AS type,
  C.CHARACTER_MAXIMUM_LENGTH AS size,
  C.TABLE_SCHEMA as tableSchema,
  C.TABLE_CATALOG AS tableCatalog,
  C.TABLE_SCHEMA as tableDatabase,
  C.COLUMN_DEFAULT as defaultValue,
  C.IS_NULLABLE as isNullable,
  C.ORDINAL_POSITION,
  (
    CASE
      WHEN C.COLUMN_KEY = 'PRI' THEN TRUE
      ELSE FALSE
    END
  ) as isPk,
  (
    CASE
      WHEN KCU.REFERENCED_COLUMN_NAME IS NULL THEN FALSE
      ELSE TRUE
    END
  ) as isFk,
  CONCAT(
    C.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    C.TABLE_name,
    '/-##-/',
    C.COLUMN_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.COLUMNS AS C
  LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU ON (
    C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.TABLE_NAME = KCU.TABLE_NAME
    AND C.TABLE_SCHEMA = KCU.TABLE_SCHEMA
    AND C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.COLUMN_NAME = KCU.COLUMN_NAME
  )
  JOIN INFORMATION_SCHEMA.TABLES AS T ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND C.TABLE_CATALOG = T.TABLE_CATALOG
WHERE
  C.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
ORDER BY
  C.TABLE_NAME,
  C.ORDINAL_POSITION;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1155
SELECT
  f.specific_name AS name,
  f.routine_schema AS dbschema,
  f.routine_schema AS dbname,
  concat(
    case
      WHEN f.routine_schema REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_schema, '`')
      ELSE f.routine_schema
    end,
    '.',
    case
      WHEN f.routine_name REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_name, '`')
      ELSE f.routine_name
    end
  ) as signature,
  GROUP_CONCAT(p.data_type) as args,
  f.data_type AS resultType,
  CONCAT(
    f.routine_schema,
    '/-##-/',
    'functions',
    '/-##-/',
    f.specific_name
  ) AS tree,
  f.routine_definition AS source
FROM
  information_schema.routines AS f
  LEFT JOIN information_schema.parameters AS p ON (
    f.specific_name = p.specific_name
    AND f.routine_schema = p.specific_schema
    AND f.routine_catalog = p.specific_catalog
  )
WHERE
  f.routine_schema NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  f.specific_name,
  f.routine_schema,
  f.routine_name,
  f.data_type,
  f.routine_definition
ORDER BY
  f.specific_name;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1143
SELECT
  C.TABLE_NAME AS tableName,
  C.COLUMN_NAME AS columnName,
  C.DATA_TYPE AS type,
  C.CHARACTER_MAXIMUM_LENGTH AS size,
  C.TABLE_SCHEMA as tableSchema,
  C.TABLE_CATALOG AS tableCatalog,
  C.TABLE_SCHEMA as tableDatabase,
  C.COLUMN_DEFAULT as defaultValue,
  C.IS_NULLABLE as isNullable,
  C.ORDINAL_POSITION,
  (
    CASE
      WHEN C.COLUMN_KEY = 'PRI' THEN TRUE
      ELSE FALSE
    END
  ) as isPk,
  (
    CASE
      WHEN KCU.REFERENCED_COLUMN_NAME IS NULL THEN FALSE
      ELSE TRUE
    END
  ) as isFk,
  CONCAT(
    C.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    C.TABLE_name,
    '/-##-/',
    C.COLUMN_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.COLUMNS AS C
  LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU ON (
    C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.TABLE_NAME = KCU.TABLE_NAME
    AND C.TABLE_SCHEMA = KCU.TABLE_SCHEMA
    AND C.TABLE_CATALOG = KCU.TABLE_CATALOG
    AND C.COLUMN_NAME = KCU.COLUMN_NAME
  )
  JOIN INFORMATION_SCHEMA.TABLES AS T ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND C.TABLE_CATALOG = T.TABLE_CATALOG
WHERE
  C.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
ORDER BY
  C.TABLE_NAME,
  C.ORDINAL_POSITION;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1141
SELECT
  f.specific_name AS name,
  f.routine_schema AS dbschema,
  f.routine_schema AS dbname,
  concat(
    case
      WHEN f.routine_schema REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_schema, '`')
      ELSE f.routine_schema
    end,
    '.',
    case
      WHEN f.routine_name REGEXP '[^0-9a-zA-Z$_]' then concat('`', f.routine_name, '`')
      ELSE f.routine_name
    end
  ) as signature,
  GROUP_CONCAT(p.data_type) as args,
  f.data_type AS resultType,
  CONCAT(
    f.routine_schema,
    '/-##-/',
    'functions',
    '/-##-/',
    f.specific_name
  ) AS tree,
  f.routine_definition AS source
FROM
  information_schema.routines AS f
  LEFT JOIN information_schema.parameters AS p ON (
    f.specific_name = p.specific_name
    AND f.routine_schema = p.specific_schema
    AND f.routine_catalog = p.specific_catalog
  )
WHERE
  f.routine_schema NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  f.specific_name,
  f.routine_schema,
  f.routine_name,
  f.data_type,
  f.routine_definition
ORDER BY
  f.specific_name;

-- boss[boss] @ localhost [127.0.0.1]   thread: 1155
SELECT
  T.TABLE_NAME AS tableName,
  T.TABLE_SCHEMA AS tableSchema,
  T.TABLE_CATALOG AS tableCatalog,
  (
    CASE
      WHEN T.TABLE_TYPE = 'VIEW' THEN 1
      ELSE 0
    END
  ) AS isView,
  T.TABLE_SCHEMA AS dbName,
  COUNT(1) AS numberOfColumns,
  CONCAT(
    T.TABLE_SCHEMA,
    '/-##-/',
    (
      CASE
        WHEN T.TABLE_TYPE = 'VIEW' THEN 'views'
        ELSE 'tables'
      END
    ),
    '/-##-/',
    T.TABLE_NAME
  ) AS tree
FROM
  INFORMATION_SCHEMA.TABLES AS T
  LEFT JOIN INFORMATION_SCHEMA.COLUMNS AS C ON C.TABLE_NAME = T.TABLE_NAME
  AND C.TABLE_SCHEMA = T.TABLE_SCHEMA
  AND (C.TABLE_CATALOG IS NULL OR C.TABLE_CATALOG = T.TABLE_CATALOG)
WHERE
  T.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY
  T.TABLE_NAME,
  T.TABLE_SCHEMA,
  T.TABLE_CATALOG,
  T.TABLE_TYPE
ORDER BY
  T.TABLE_NAME;

-- boss[boss] @ localhost []   thread: 1156
set names utf8;

-- boss[boss] @ localhost []   thread: 1156
use mysql;

-- boss[boss] @ localhost []   thread: 1156
select * from mysql.general_log where command_type='Query';

-- boss[boss] @ localhost []   thread: 1157
set names utf8;

-- boss[boss] @ localhost []   thread: 1157
use mysql;

-- boss[boss] @ localhost []   thread: 1157
select * from mysql.general_log where command_type='Query';

-- boss[boss] @ localhost []   thread: 1158
set names utf8;

-- boss[boss] @ localhost []   thread: 1158
use mysql;

-- boss[boss] @ localhost []   thread: 1158
select * from mysql.general_log where command_type='Query';

-- boss[boss] @ localhost []   thread: 1159
set names utf8;

-- boss[boss] @ localhost []   thread: 1159
use mysql;

-- boss[boss] @ localhost []   thread: 1159
select * from mysql.general_log where command_type='Query';

