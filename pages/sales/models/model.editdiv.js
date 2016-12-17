{ "table": "sales_divdetails",
  "select_row": "select * from $table where cid=:cid and division=:division and syear=:year",
  "primary_key": "cid,division,syear",
  "beforeUpdate": "beforeDivisionUpdate"
}
