{ "table": "sales_companies",
  "select": "select cid as id,name,industry_group,industry,sector,isin,region,sales,reviewed from $table $where $order $limit",
  "list_columns": "name,industry_group,industry,sector,isin,region,sales",
  "primary_key": "cid",
  "default_order":"cid",
  "search":"name like :search or isin like :search or cid like :search",
  "rows_number_limit": 10,
  "beforeUpdate":"beforeUpdateCompany",
  "select_row": "select * from $table where cid=:cid",
  "select_total": "select count(*) from $table $where"
}
