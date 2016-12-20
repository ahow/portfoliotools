{ "table": "sales_sic",
  "select": "select * from $table $where $order $limit",
  "list_columns": "id,name,description",
  "primary_key": "id",
  "default_order":"id",
  "search":"name like :search or description like :search",
  "rows_number_limit": 8,
  "select_row": "select * from $table where id=:id",
  "select_total": "select count(*) from $table $where",
  "afterLoad":"afterLoadSIC"
}
