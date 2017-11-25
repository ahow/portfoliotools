{ "table": "sales_divdetails",
  "select": "select d.*, s.name from $table d join sales_sic s on d.sic=s.id $where $order",
  "default_order":"syear desc, sic",
  "select_row": "select * from $table where cid=:cid and division=:division and syear=:year",
  "primary_key": "cid,division,syear",
  "beforeUpdate": "beforeDivisionUpdate",
  "delete": "delete from $table where cid=:cid and division=:division and syear=:year",
  "filter_parts":{
      "cid":"d.cid=:cid",
      "division":"d.division=:division"
  }
}
