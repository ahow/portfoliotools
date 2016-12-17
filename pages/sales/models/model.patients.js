{ "table": "hha_patients",
  "select": "select * from $table $where $order $limit",
  "list_columns": "created,firstname,lastname,date_of_birth,email",
  "primary_key": "id",
  "default_order":"id desc",
  "select_row": "select * from $table where id=:id",
  "select_total": "select count(*) from $table $where",
  "beforeInsert": "bi_Patient"
}
