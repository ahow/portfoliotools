{ "table": "sales_companies",
  "select": "select cid as id,name,industry_group,industry,sector,isin,region,sales as sales_bn,reviewed from $table $where $order $limit",
  "list_columns": "name,industry_group,industry,sector,isin,region,sales_bn",
  "primary_key": "cid",
  "default_order":"cid",
  "search":"name like :search or isin like :search or cid like :search",
  "rows_number_limit": 7,
  "beforeUpdate":"beforeUpdateCompany",
  "select_row": "select * from $table where cid=:cid",
  "select_total": "select count(*) from $table $where",
  "onCustomFilter":"onCustomFilterCompanies",
  "filter_parts":{
      "fregion":"region=:fregion",
      "sic":"sic=:sic",
      "industry_group":"industry_group=:industry_group",
      "subsector":"subsector=:subsector",
      "major_group":"cid in (select d.cid from sales_divdetails d where d.sic in (select s.id from sales_sic s where s.industry_group_id in (select g.id from sales_industry_groups g where g.major_group=:major_group)))",
      "division":"cid in (select d.cid from sales_divdetails d where d.sic in (select s.id from sales_sic s where s.industry_group_id in (select g.id from sales_industry_groups g where g.division=:division)))",
      "sic_code":"cid in (select d.cid from sales_divdetails d where d.sic=:sic_code)"
  }
}
