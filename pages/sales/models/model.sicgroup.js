{ "table": "sales_sic",
  "select_row": "select s.name as industry, g.division as sic_division, g.major_group, g.industry_group  from $table s join sales_industry_groups g on s.industry_group_id=g.id where s.id=:id",
  "primary_key": "id"
}
