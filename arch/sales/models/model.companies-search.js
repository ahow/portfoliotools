{ "table": "sales_companies",
  "select": "select cid as id,name as company,sector,isin,region from $table $where $order $limit",
  "list_columns": "company,sector,isin,region",
  "primary_keys": "id",
  "default_order":"name",
  "search":"name like :search or isin like :search or sector like :search",
  "rows_number_limit": 8,
  "select_row": "select * from $table where id=:id",
  "select_total": "select count(*) from $table $where"
}
