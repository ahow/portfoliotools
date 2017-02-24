{ "table": "sales_portfolio_summaries",
  "select": "select id,created,description from $table $where $order $limit",
  "list_columns": "description",
  "primary_key": "id",
  "default_order":"id",
  "search":"name like :description",
  "rows_number_limit": 5,
  "select_row": "select * from $table where id=:id",
  "select_total": "select count(*) from $table $where"
}
