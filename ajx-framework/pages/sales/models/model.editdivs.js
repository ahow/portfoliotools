{ "table": "sales_divdetails d",
  "select": "select d.*, s.name from $table join sales_sic s on d.sic=s.id $where $order",
  "default_order":"syear desc, sic",
  "select_row": "select * from $table where cid=:cid and division=:division and syear=:year",
  "primary_key": "cid,division,syear",
  "beforeUpdate": "beforeDivisionUpdate",
  "delete": "delete from $table where cid=:cid and division=:division and syear=:year",
  "filter_parts":{
      "cid":"d.cid=:cid",
      "division":"d.division=:division"
  },
  "allow_update": ["editor","admin"],
  "allow_insert": ["editor","admin"],
  "allow_delete": ["editor","admin"]
}
