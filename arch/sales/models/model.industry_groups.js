{ "table": "sales_industry_groups",
  "select": "select * from $table $where $order $limit",
  "list_columns": "id,industry_group,division,major_group",
  "primary_keys": "id",
  "default_order":"id",
  "search":"industry_group like :search",
  "rows_number_limit": 12,
  "select_row": "select * from $table where id=:id",
  "select_total": "select count(*) from $table $where"
}
