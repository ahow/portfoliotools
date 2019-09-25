{ "table": "sales_companies",
  "select": "select distinct c.subsector as id, c.subsector from $table c $where $order $limit",
  "list_columns": "subsector",
  "primary_key": "id",
  "default_order":"1",
  "search":"c.subsector like :search",
  "rows_number_limit": 8,
  "select_row": "select * from $table where id=:id",
  "select_total": "select  count(*) from (select distinct c.subsector from sales_companies c $where order by c.subsector) t",
  "filter_parts":{
    "sector":"sector=:sector"
  }
}
