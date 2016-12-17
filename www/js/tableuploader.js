$(function()
{  

  function renderTable(d)
  { var rows = d.split("\n");
    var s = '<table class="table table-striped">';
    if (rows.length>0)
    { var tds = rows[0].split("\t");
      s+='<tr>';
      for  (var i=0; i<tds.length; i++)
      {  s+='<th>'+tds[i]+'</th>';
      }
      s+='</tr>';  
      for (var i=1; i<rows.length; i++)
      {   var tds = rows[i].split("\t");
          if (tds.length>0 && (rows[i].trim()!='') )
          {   s+='<tr>';
              for (var j=0; j<tds.length; j++)
              {  s+='<td>'+tds[j]+'</td>';
              }
              s+='</tr>';  
          }
      }
      s+='</table>';
      $('#preview').html(s);
      $('#bcopy').removeClass('hidden');  
    }
  }

  $('#bsend').click(function(){
      renderTable( $('#cells').val() );
  });

  $('#bcopy').click(function(){
     document.execCommand('copy');
  });
    
  document.addEventListener('paste', function(e)
  {  //console.log(e);
     renderTable( e.clipboardData.getData('text/plain') );     
  });

  document.addEventListener('copy', function(e)
  {  e.preventDefault();     
     e.clipboardData.setData('text/plain', $('#preview').html() );
  });
  
}
);
