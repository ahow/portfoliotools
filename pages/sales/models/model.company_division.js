{ "table": "sales_industry_groups",
  "select": "select distinct division as id, division as name from $table $where $order",
  "list_columns": "name",
  "primary_key": "id",
  "default_order":"division",
  "search":"industry_group like :search",
  "select_row": "select * from $table where id=:id",
  "select_total": "select count(*) from $table $where"
}
