{ "table": "sales_industry_groups",
  "select": "select distinct major_group as id, major_group as name from $table $where $order",
  "list_columns": "name",
  "primary_key": "id",
  "default_order":"major_group",
  "search":"subsector like :search",
  "select_row": "select * from $table where id=:id",
  "select_total": "select count(*) from $table $where",
  "allow_update": ["editor","admin"],
  "allow_insert": ["editor","admin"],
  "allow_delete": ["editor","admin"]
  
}
