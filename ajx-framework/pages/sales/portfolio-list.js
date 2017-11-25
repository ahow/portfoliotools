
$(function(){
    var pCSV = new previewCSV('#preview_portfolio','#add_portfolio');
    var model = new modelListController('.model-list', portfolioTableView);    
    model.load();
    model.click(function(e, row){ 
        //console.log(row);
    });
    
    // Draw portfolio
    function portfolioTableView(selector,d,onclick,ondblclick)
    {  var s = '';
       var i;
       var edit = [false,true,true,false];
       var names = [,'portfolio','description']
       // name="me" 
       
       if (d.titles!=undefined)
       {   var h = '<tr>';
           for (i in d.titles)
           { h+='<th>'+d.titles[i]+'</th>';               
           }
           h+='<th>&nbsp;</th></tr>';
           $(selector).find('thead').html(h);
       }
       for (i in d.rows)
       {   var j;
           var r = d.rows[i];
           if (r.id!=undefined) s+='<tr data-id="'+i+'">'; else s+='<tr>';
           for (j in d.columns) 
           {   var v = r[ d.columns[j] ];
               if (edit[j])  s+='<td contenteditable="true" data-old-value="'+v+'" name="'+names[j]+'">'+v+'</td>'; 
               else s+='<td>'+v+'</td>';
           }
           s+='<td><div class="btn-group  pull-right"><button class="btn btn-sm"><a target="_blank" href="/html.php/pages/sales/portfolio.csv?id='+r.id+'">Download CSV</a></button>'+
           '<button class="btn btn-sm btn-danger b-delete">Delete</button></div></td></tr>';
       }
       $(selector).find('tbody').html(s);
       if (onclick!=null)  $(selector+' tbody tr').click(function(row){
           $(row.target).parents('table:first').find('tr').removeClass('active');
           var id = $(row.target).parents('tr:first').addClass('active').attr('data-id');   
           onclick(row, d.rows[id]);
       });
       
       if (ondblclick!=undefined && ondblclick!=null)  $(selector+' tbody tr').dblclick(function(row){
           $(row.target).parents('table:first').find('tr').removeClass('active');
           var id = $(row.target).parents('tr:first').addClass('active').attr('data-id');   
           ondblclick(row, d.rows[id]);
       });
       
       $(selector).find('.b-delete').click(function(row)
       {   var id = $(row.target).parents('tr:first').addClass('active').attr('data-id');
           var r = d.rows[id];
           if (confirm('Delete '+r.portfolio+' ('+r.created+') ?')) 
           {  ajx('/pages/sales/Model/portfolio/delete', {id:r.id}, function(d){
                   if (!d.error) 
                   { setOk('Row Deleted');
                     model.load();
                   }
              });
           }
       });
      
       $(selector+' tbody tr td').blur(function(e){
            var td = $(e.target);
            var value = td.html();
            if (td.attr('data-old-value')!=value)
            {  // value  changed 
               var name = td.attr('name');
               if (name=='readonly') return;
               var prow = $(e.target).parents('tr:first');
               var id = prow.attr('data-id'); 
               var r = {id:d.rows[id].id};
               r[name] = value;               
               ajx('/pages/sales/Model/portfolio/update', r, function(d){                   
                   td.attr('data-old-value', value);
                   if (!d.error) setOk(d.info); 
               });
            }
        });
       
    }
    
});
