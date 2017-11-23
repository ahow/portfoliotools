{ "table": "sales_companies",
  "select": "select distinct subsector as id, subsector as name from $table $where $order",
  "list_columns": "name",
  "primary_key": "id",
  "default_order":"subsector",
  "search":"subsector like :search",
  "select_row": "select * from $table where id=:id",
  "select_total": "select count(*) from $table $where",
  "allow_update": ["editor","admin"],
  "allow_insert": ["editor","admin"],
  "allow_delete": ["editor","admin"]
}
