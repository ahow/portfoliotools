{ "table": "sales_companies",
  "select": "select distinct c.sector as id, c.sector from $table c $where $order $limit",
  "list_columns": "sector",
  "primary_key": "id",
  "default_order":"1",
  "search":"c.sector like :search",
  "rows_number_limit": 8,
  "select_row": "select * from $table where id=:id",
  "select_total": "select  count(*) from (select distinct c.sector from sales_companies c $where order by c.sector) t"
}
