var edit;

// Custom view fabric
function createCustomModelView(_html, _init)
{   var html = _html;
    var init = _init;
    
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
           s+='<td>'+html+'</td></tr>';
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
       if (init!=undefined) init(d);
    }
    
    return modelPortfolioView;
}

function editPortfolioSummary(selector){
    var id=null, name=null, insert_id = null, onaftersave = null;
    
    function show()
    {   $(selector+' .modal').modal('show');
    }
    
    function edit(row_id)
    {  clear();
       insert_id = row_id;
      
       function fillTable(sel, d)
       {  var i, j;
          var s = '<tr><th>Series</th>';
          for (i=0; i<d.columns.length; i++) s+='<th contenteditable="true">'+d.columns[i]+'</th>';
          s+='</tr>';
          $(sel+' thead').html(s);
          
          s='';
          for (i=0; i<d.series.length; i++) 
          { var r = d.series[i];
            s+='<tr><th contenteditable="true">'+r.name+'</th>';
            for (j=0; j<r.data.length; j++) s+='<td contenteditable="true">'+r.data[j]+'</td>';
            s+='</tr>';
          }
          $(sel+' tbody').html(s);          
       }

       ajx('/pages/sales/LoadPortfolioSummaries', {id:row_id}, function(d){
            console.log(d);
            $(selector+' .modal .pfname').html(d.row.portfolio);
            id = d.row.portfolio_id;
            $(selector+' .modal #description').val(d.row.description);
            if (d.row.bar!=undefined) fillTable(selector+' .bar-chart', d.row.bar);
            if (d.row.line!=undefined) fillTable(selector+' .line-chart', d.row.line);
            if (d.row.options!=undefined)
            {     var i;
                  var s = '';
                  for (i=0; i<d.row.options.length; i++)
                  { var chk;
                    if (d.row.options[i].checked=='true') chk = ' checked '
                    s+='<tr><td><input type="checkbox" '+chk+'/></td>'+
              '<td contenteditable="true">'+d.row.options[i].name+'</td>'+
              '<td><button class="btn btn-sm b-del">Delete</button></td></tr>';
                  }                
                  $(selector+' .opt-list').html(s);                  
                  $(selector+' .opt-list .b-del').click(deleteOption);
            }
            show();
       }); 
      
       
    }
    
    
    function setPortfolio(_id, _name)
    {   id = _id;
        name = _name;
        $(selector+' .modal .pfname').html(name);
    }
    
    function addNew()
    {  clear();
       show();
    } 
    
    function clear()
    { insert_id = null;
      $(selector+' .modal #description').val('');
      $(selector+' .bar-chart thead').html('<tr><th>Series</th></tr>');
      $(selector+' .bar-chart tbody').html('');
      $(selector+' .line-chart thead').html('<tr><th>Series</th></tr>');
      $(selector+' .line-chart tbody').html('');
      $(selector+' tbody.opt-list').html('');
    }
    
    function save()
    { var d = {};
      d.description = $(selector+' .modal #description').val();
      d.portfolio_id = id;
      d.options = [];
      var rows = $(selector+' .opt-list tr');
      var i = 0;
      for (i=0; i<rows.length; i++) 
      {   var tds = $(rows[i]).find('td');          
          d.options.push({name:tds[1].innerHTML, checked:$(tds[0]).find('input')[0].checked});
      }
      
      function getColumns(cname)
      { var columns = [];
        var cols = $(selector+' '+cname+' thead th');
        for (i=1; i<cols.length; i++) columns.push(cols[i].innerHTML);
        return columns;
      }
      
      function getSeries(cname)
      {   var rows = $(selector+' '+cname+' tbody tr');
          var series = [];
          for (i=0; i<rows.length; i++) 
          {   var tds = $(rows[i]).find('td');
              var data = [];
              for (var j=0; j<tds.length; j++) data.push(tds[j].innerHTML);
              series.push({name:$(rows[i]).find('th')[0].innerHTML, data:data});
          }
          return series;
      }
      
      
      d.bar = {};
      d.bar.columns = getColumns('.bar-chart');
      d.bar.series = getSeries('.bar-chart');
      
      d.line = {};
      d.line.columns = getColumns('.line-chart');
      d.line.series = getSeries('.line-chart');
      
      if (insert_id!=null) d.id = insert_id;

      ajx('/pages/sales/SavePortfolioSummaries', d, function(dd){                   
            if (!dd.error) setOk(dd.info); 
            if (dd.insert_id!=undefined) insert_id = dd.insert_id;
            if (!dd.error && onaftersave!=null) onaftersave(dd); 
       });
            
     // console.log(d);
      
    }
    
    function deleteOption(e)
    {  setTimeout(function(){
            $(e.target).parent().parent().remove();
        },100);
    }
    
    function afterSave(foo){ onaftersave=foo; }
    
    $(selector+' .b-add-category').click(function(){
          $(selector+' .opt-list').append('<tr><td><input type="checkbox" /></td>'+
          '<td contenteditable="true">Option</td>'+
          '<td><button class="btn btn-sm b-del">Delete</button></td></tr>');          
          $(selector+' .opt-list tr:last .b-del').click(deleteOption);
    });
    
    $(selector+' .b-save').click(function(){        
        save();  
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
    
    
           
    return {show:show, setPortfolio:setPortfolio, save:save,
        afterSave:afterSave, edit:edit, addNew:addNew};
}


$(function(){
     
  // var dialog;
  var views = new htviewCached();
  
 
   var pager;  
   

   var view = new createCustomModelView('<div class="btn-group pull-right">\
   <button class="btn btn-sm b-new">New summary</button>\
   </div>', function(){      
      $('button.b-new').click(function(e){
            edit.addNew();      
      });
   });
   
   var model = new modelListController('#tabpflist .model-list', view);
   
   model.load();
   model.click(function(e, row){
       edit.setPortfolio(row.id, row.portfolio);
   });
   
    // enable pager
   pager = new modelPagination('#tabpflist .model-list .model-pager');
   
   model.total(function(total, rows_lim){
       pager.setTotal(total, rows_lim);
   })
   pager.change(function(n){
       model.load(n);

   });
   
   var model_sum = null;
   var model_view = new createCustomModelView('<div class="btn-group pull-right">\
   <button class="btn btn-sm btn-primary b-edit">Edit</button>\
   <button class="btn btn-sm btn-danger b-delete">Delete</button>\
   </div>', function(d){
        $('button.b-delete').click(function(e){
            var id = $(e.target).parents('tr:first').addClass('active').attr('data-id');
            var r = d.rows[id];
            if (confirm('Delete '+r.description+'?')) 
            {  ajx('/pages/sales/Model/portfolio-summaries/delete', {id:r.id}, function(d){
                   if (!d.error) 
                   { setOk('Row Deleted');
                     model_sum.load();
                   }
              });
            }
        });
        $('button.b-edit').click(function(e){
            var id = $(e.target).parents('tr:first').addClass('active').attr('data-id');
            var r = d.rows[id];
            edit.edit(r.id);
        });
       
   });
   
   model_sum = new modelListController('#tabedit .model-list', model_view);
   model_sum.load();
   model_sum.last_id = null;
   model_sum.click(function(e, row){
        if (model_sum.last_id!=row.id)
        {  model_sum.last_id=row.id;
           ajx('/pages/sales/LoadPortfolioSummaries', {id:row.id}, function(d){
                                
                views.view('/pages/sales/pfsummary','#pfsummary', function(){
                    var i;
                    if (d.row.options!=undefined)
                    {  var s = '<tr>';
                        for (i=0; i<d.row.options.length; i++)
                        {   var r = d.row.options[i];
                            if (r.checked=='false') s+='<td class="disabled">'+r.name+'</td>';
                            else  s+='<td>'+r.name+'</td>';
                        }
                        s += '<tr>';
                        $('#p-options').html(s);
                    }
                    if (d.row.portfolio!=undefined) $('#p-name').html(d.row.portfolio);
                    
                    // console.log('view: ',d);
    
                });

                // if (dd.insert_id!=undefined) insert_id = dd.insert_id;
           });
        }
   });
   
   var pager_sum = new modelPagination('#tabedit .model-pager');
   model_sum.total(function(total, rows_lim){
       pager_sum.setTotal(total, rows_lim);
   })
   pager_sum.change(function(n){
       model_sum.load(n);
   });
   

   views.view('/pages/sales/editpfsummary','#editpfsum', function(){
       edit = new editPortfolioSummary('#editpfsum');
       edit.afterSave(function(d){
            model_sum.load();
            $('#tbedit a[href="#tabedit"]').tab('show');
       });
    });
    
   
   
});

