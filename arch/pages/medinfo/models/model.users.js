{ "table": "mc_users",
  "select": "select id,name,firstname,lastname,email from $table $where $order $limit",
  "list_columns": "name,firstname,lastname,email",
  "primary_keys": "id",
  "select_row": "select * from $table where id=:id",
  "select_total": "select count(*) from $table $where"
}
