{ "table": "sales_portfolio",
  "select": "select id,portfolio,description,created from $table $where $order $limit",
  "list_columns": "created,portfolio,description",
  "primary_key": "id",
  "default_order":"id desc",
  "search":"portfolio like :search or description like :search",
  "rows_number_limit": 10,  
  "select_row": "select * from $table where id=:id",
  "select_total": "select count(*) from $table $where",
  "delete":"delete from $table where id=:id"
}
