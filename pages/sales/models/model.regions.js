{ "table": "sales_companies",
  "select": "select distinct region as id, region as name from $table $where $order",
  "list_columns": "region",
  "primary_key": "cid",
  "default_order":"region",
  "search":"region like :search",
  "select_row": "select * from $table where cid=:id",
  "select_total": "select count(*) from $table $where"
}
