{ "table": "hha_patients",
  "select": "select * from $table $where $order $limit",
  "list_columns": "firstname,lastname,date_of_birth,email",
  "primary_keys": "id",
  "rows_number_limit": 10,
  "default_order":"id desc",
  "search":"firstname like :search or lastname like :search or email like :search",
  "select_row": "select * from $table where id=:id",
  "select_total": "select count(*) from $table $where",
  "beforeInsert": "bi_Patient"
}
