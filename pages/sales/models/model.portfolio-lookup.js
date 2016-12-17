{ "table": "sales_portfolio",
  "select": "select id,portfolio as name,description from $table $where $order",
  "list_columns": "id,name,description",
  "primary_key": "id",
  "default_order":"name",
  "search":"portfolio like :search or description like :search",
  "select_row": "select * from $table where id=:id",
  "select_total": "select count(*) from $table $where"
}
