{ "table": "sales_companies",
  "select": "select cid as id,name,industry_group,industry,sector,isin,region from $table $where $order $limit",
  "list_columns": "name,industry_group,industry,sector,isin,region",
  "primary_keys": "id",
  "default_order":"cid",
  "search":"name like :search or isin like :search",
  "rows_number_limit": 10,
  "select_row": "select * from $table where id=:id",
  "select_total": "select count(*) from $table $where"
}
