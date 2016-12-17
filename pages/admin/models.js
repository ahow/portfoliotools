{ "users": { 
    "table":"mc_users", 
    "query":"select * from mc_users $where order by id desc",
    "where":"lastname like :search or firstname like :search or name like :search or email like :search"
   },
  "signup": { 
    "table":"signup", 
    "query":"select * from signup order by id desc"
   },
   "evnamegl": { 
    "table":"evnamegl", 
    "query":"select e.id, e.created, e.firstname, e.lastname, e.email, group_concat(concat(' ',g.firstname,' ',g.lastname)) AS guests from evnamegl e join evnamegl_guests g on g.engl_id=e.id group by g.engl_id order by id desc"
    },
   "evnamegl_guests": {
       "table":"evnamegl_guests",
       "query": "select * from evnamegl_guests where engl_id=:id order by id"
   },
   "vipreserv": {
       "table":"vipreserv",
       "query":"select v.id, v.created, v.firstname, v.lastname, v.email, v.bookdate, v.phone, v.vippackage_id as package, group_concat(concat(' ',g.firstname,' ',g.lastname)) AS guests from vipreserv v join vipreserv_guests g on g.vipreserv_id=v.id group by g.vipreserv_id order by id desc"
   },
   "usergroups": {
       "query":"select g.id,g.grname,ug.user_id from mc_groups g left outer join mc_usergroups ug on g.id=ug.group_id and ug.user_id=:id order by g.grname"
   }
}
