{ "table": "sales_divdetails",
  "select_row": "select * from $table where cid=:cid and division=:division and syear=:year",
  "primary_key": "cid,division,syear",
  "beforeUpdate": "beforeDivisionUpdate",
  "delete": "delete from $table where cid=:cid and division=:division",
  "allow_update": ["editor","admin"],
  "allow_delete": ["editor","admin"]
}
