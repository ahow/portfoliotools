{ "table": "mc_users",
  "select": "select * from $table $where $order $limit",
  "list_columns": "name,email,firstname,lastname",
  "primary_key": "id",
  "default_order":"created desc",
  "search":"name like :search or firstname like :search or lastname like :search or email like :search",
  "rows_number_limit": 8,
  "delete":"delete from $table where id=:id",
  "select_row": "select * from $table where id=:id",
  "select_total": "select count(*) from $table $where",
  "beforeInsert": "beforeInsertUser"
}
