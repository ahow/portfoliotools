/*
 * header example:
 * var h = [{title:"Full name",f:"fullname",ondraw:function(v, r){ 
 *              return '<b data-id="'+r.id+'">'+v+:</b>"; } 
 *          },
 *          {title: "Last Name", f:"last_name" }
 *         ];
 */ 

function arrayListTable(selector, _header)
{  var pager = null;
   var rows_onpage = 10;
   var d = null;
   var rows_total = 0;
   var ondrawcell = null;
   var header = null;
   
   pager = new modelPagination(selector+' .list-pager');
   pager.change(function(page){
        //console.log(page);
        drawPage(page);
   });

   function toSgFloat(v, decimals)
   { var n = 1.0*v;            
      if (isNaN(n) || v==null)
      {   return '-';
      }
      if (n>0) return '+'+n.toFixed(decimals);
      else return n.toFixed(decimals);
   }   
 
   function setHeader(h)
   { header = h;
     var s = '';
     for (var i=0; i<h.length; i++) s+='<th>'+h[i].title+'</th>';
     s+='</tr>';
     $(selector+' .table thead').html(s);   
   }
 
   function setData(data)
   {   d = data;      
       rows_total = d.length;
       pager.setTotal(rows_total, rows_onpage);
       var h = header;
       if (_header!=undefined) setHeader(_header);
       drawPage(1);
   }
   
   function drawPage(p)
   {  var s = '';      
      var start = (p-1)*rows_onpage;            
      // console.log(d);
      for (i=start; i<(start+rows_onpage) && i<rows_total; i++)
      { s +='<tr>';
        for (var j=0; j<header.length; j++)
        {   var h = header[j];
            var v = 'none';
            if (d[i][h.f]!=undefined) v = d[i][h.f];
            if (h.ondraw!=undefined) s+='<td>'+h.ondraw(v, d[i])+'</td>';
            else s+='<td>'+v+'</td>';
        }        
        s +='</tr>';
      }
      $(selector+' .table tbody').html(s);
   }
   
   return {setData:setData, setHeader:setHeader }
}
