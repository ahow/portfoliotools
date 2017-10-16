{ "table": "sales_companies",
  "select": "select distinct industry_group as id, industry_group as name from $table $where $order",
  "list_columns": "name",
  "primary_key": "id",
  "default_order":"industry_group",
  "search":"industry_group like :search",
  "select_row": "select * from $table where id=:id",
  "select_total": "select count(*) from $table $where"
}
