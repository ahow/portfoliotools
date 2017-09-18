{ "table": "mc_users",
  "select": "select * from $table $where $order $limit",
  "list_columns": "name,email,firstname,lastname",
  "primary_key": "id",
  "default_order":"name",
  "search":"name like :search or isin like :search or sector like :search or cid like :search",
  "rows_number_limit": 8,
  "select_row": "select * from $table where id=:id",
  "select_total": "select count(*) from $table $where",
  "delete":"delete from $table where id=:id",
  "beforeInsert": "beforeInsertUser"
}
