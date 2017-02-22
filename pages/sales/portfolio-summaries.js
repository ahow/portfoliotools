var edit;

function modelPortfolioView(selector,d,onclick,ondblclick)
{  var s = '';
   var i;
   
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
       for (j in d.columns) s+='<td>'+r[ d.columns[j] ]+'</td>';
       s+='<td><div class="btn-group pull-right"><button class="btn btn-sm b-new">New summary</button></div></td></tr>';
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
   
   $('button.b-new').click(function(e){
       edit.show();
       // $('#editpfsum .modal').modal('show');
       // console.log(e.target)
   });
}

function editPortfolioSummary(selector){
    function show(id)
    {   $(selector+' .modal').modal('show');
    }
    
    $(selector+' .b-add-category').click(function(){
          $(selector+' .opt-list').append('<tr><td><input type="checkbox" /></td>'+
          '<td contenteditable="true">Option</td>'+
          '<td><button class="btn btn-sm b-del">Delete</button></td></tr>');
          
          $(selector+' .opt-list tr:last .b-del').click(function(e){
              setTimeout(function(){
                    $(e.target).parent().parent().remove();
              },100);
          });
    });
    
    $(selector+' .b-add-bar-column').click(function(){
         $(selector+' .bar-chart thead tr').append('<th contenteditable="true"></th>');
         $(selector+' .bar-chart tbody tr').append('<td contenteditable="true"></td>');
    });    
    
    $(selector+' .b-add-bar-row').click(function(){
         var n = $(selector+' .bar-chart thead th').length;
         var s = '<tr><th contenteditable="true"></th>';
         for (var i=1; i<n; i++) s+='<td contenteditable="true"></td>';
         s+='</tr>';         
         $(selector+' .bar-chart tbody').append(s);
    });   

    $(selector+' .b-add-line-column').click(function(){
         $(selector+' .line-chart thead tr').append('<th contenteditable="true"></th>');
         $(selector+' .line-chart tbody tr').append('<td contenteditable="true"></td>');
    });    
    
    $(selector+' .b-add-line-row').click(function(){
         var n = $(selector+' .line-chart thead th').length;
         var s = '<tr><th contenteditable="true"></th>';
         for (var i=1; i<n; i++) s+='<td contenteditable="true"></td>';
         s+='</tr>';         
         $(selector+' .line-chart tbody').append(s);
    }); 
           
    return {show:show};
}


$(function(){
     
  // var dialog;
  var views = new htviewCached();
  
        
  if ($('.model-list').length>0)
  { 
       var pager;       
       var model = new modelListController('.model-list', modelPortfolioView);
       
       model.load();
       model.click(function(e, row){
           // console.log(row);
           $('#sic').val(row.id);
          //  marketRanking(row.id);
           $('#tbedit').removeClass('disabled');
       });
       
        // enable pager
       pager = new modelPagination('.model-list .model-pager');
       
       model.total(function(total, rows_lim){
           pager.setTotal(total, rows_lim);
       })
       pager.change(function(n){
           model.load(n);
       });
       
   }
   
   views.view('/pages/sales/editpfsummary','#editpfsum', function(){
       edit = new editPortfolioSummary('#editpfsum');
    });
   
   
});

