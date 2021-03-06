var edit;

function deleteOption(e)
{  setTimeout(function(){
        $(e.target).parent().parent().remove();
    },100);
}
    
// Custom view fabric
function createCustomModelView(_html, _init)
{   var html = _html;
    var init = _init;
    
    function modelPortfolioView(selector,d,onclick,ondblclick)
    {  var s = '';
       var i;
       // replace format {name} to row value
       function formatS(row)
       {   var myRe = /{\w+\}*/g;
           var str = html;
           var a;
           var r = html;
           while ((a = myRe.exec(str)) !== null) 
           {
             var p = a[0];
             var nm = p.replace('{','').replace('}','');
             r = r.replace(p, row[nm]);
           }
           return r;
       }

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
           s+='<td>'+formatS(r)+'</td></tr>';
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


function editSettings(selector, onloaded)
{
    $(selector+' .b-add-metric-row').click(function(){
          $(selector+' .metrics-list').append('<tr>\
         <td><div class="bs-model-select" data-model="/pages/sales/Model/metric-lookup">\
         <select class="form-control w-metric" data-control-type="basic"></select></div></td>'+          
          '<td><input type="number" /></td>'+
          '<td><input type="number" /></td>'+
          '<td><button class="btn btn-sm b-del btn-danger">Delete</button></td></tr>');
          new mdSelect(selector+' .metrics-list .w-metric:last');
          $(selector+' .metrics-list tr:last .b-del').click(deleteOption);
    });
    
    ajx('/pages/sales/LoadPortfolioSummariesSettings', {}, function(d){
        if (d.row!=undefined && d.row.metrics!=undefined)
        {     var i;
                  var s = '';
                  for (i=0; i<d.row.metrics.length; i++)
                  { var chk;
                    var m = d.row.metrics[i];
                    s+='<tr>\
         <td><div class="bs-model-select" data-model="/pages/sales/Model/metric-lookup">\
         <select class="form-control w-metric" data-control-type="basic"></select></div></td>'+
          '<td><input type="number" value="'+m.min+'"/></td>'+
          '<td><input type="number" value="'+m.max+'" /></td>'+
          '<td><button class="btn btn-sm b-del btn-danger">Delete</button></td></tr>';
                  }                
                  $(selector+' .metrics-list').html(s);                  
                  $(selector+' .metrics-list .b-del').click(deleteOption);
                  var sels = $(selector+' .metrics-list .w-metric');
                  for (i=0; i<d.row.metrics.length; i++)
                  { var m = d.row.metrics[i];
                    new mdSelect(selector+' .metrics-list .w-metric:eq('+i+')', m.id);
                  }                  

        }
        if (onloaded!=undefined) onloaded(d);
    });
    
    function getData()
    {  var d = {};
       d.metrics = [];
       var rows = $(selector+' .metrics-list tr');
       for (i=0; i<rows.length; i++) 
       {   var tr = $(rows[i]);
           d.metrics.push({id:tr.find('.w-metric').val(), min:tr.find('input:first').val(),
           max:tr.find('input:last').val()});
       }
       
       d.metric_id = $(selector+' #social_value_metric').val();
       d.esg_metric_id = $(selector+' #esg_score').val();
       return d; 
    }
    
    function save()
    {  var d = getData();
        
       ajx('/pages/sales/SavePortfolioSummariesSettings', {data:d}, function(dd){
           if (!dd.error) setOk(dd.info);           
       });
       
      
    }
    
    $(selector+' .b-save-settings').click(save);
    
    return {save:save, getData:getData};
}


function editSnapshotSettings(selector, onloaded)
{    $(selector+' .b-add-metric-row').click(function(){
          $(selector+' .metrics-list').append('<tr>\
         <td><div class="bs-model-select" data-model="/pages/sales/Model/metric-lookup">\
         <select class="form-control w-metric" data-control-type="basic"></select></div></td>'+          
          '<td><button class="btn btn-sm b-del btn-danger">Delete</button></td></tr>');
          new mdSelect(selector+' .metrics-list .w-metric:last');
          $(selector+' .metrics-list tr:last .b-del').click(deleteOption);
    });
    
    ajx('/pages/sales/LoadSnapshotSummariesSettings', {}, function(d){
        if (d.row!=undefined && d.row.metrics!=undefined)
        {     var i;
                  var s = '';
                  for (i=0; i<d.row.metrics.length; i++)
                  { var chk;
                    var m = d.row.metrics[i];
                    s+='<tr>\
         <td><div class="bs-model-select" data-model="/pages/sales/Model/metric-lookup">\
         <select class="form-control w-metric" data-control-type="basic"></select></div></td>'+
          '<td><button class="btn btn-sm b-del btn-danger">Delete</button></td></tr>';
                  }                
                  $(selector+' .metrics-list').html(s);                  
                  $(selector+' .metrics-list .b-del').click(deleteOption);
                  var sels = $(selector+' .metrics-list .w-metric');
                  for (i=0; i<d.row.metrics.length; i++)
                  { var m = d.row.metrics[i];
                    new mdSelect(selector+' .metrics-list .w-metric:eq('+i+')', m.id);
                  }
                  $(selector+' #ss_comparison').val(d.row.comparison_id);

        }
        if (onloaded!=undefined) onloaded(d);
    });
    
    function save()
    {  var d = getData();
        
       ajx('/pages/sales/SaveSnapshotSummariesSettings', {data:d}, function(dd){
           if (!dd.error) setOk(dd.info);           
       });
    }
    function getData()
    {  var d = {};
       d.metrics = [];
       var rows = $(selector+' .metrics-list tr');
       for (i=0; i<rows.length; i++) 
       {   var tr = $(rows[i]);
           d.metrics.push({id:tr.find('.w-metric').val()});
       }
       d.comparison_id = $(selector+' #ss_comparison').val();
       return d; 
    }
    $(selector+' .b-save-settings').click(save);    
   return {save:save, getData:getData};
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
          if (d.columns!=undefined) for (i=0; i<d.columns.length; i++) s+='<th contenteditable="true">'+d.columns[i]+'</th>';
          s+='</tr>';
          $(sel+' thead').html(s);
          
          s='';
          if (d.series!=null) 
          for (i=0; i<d.series.length; i++) 
          { var r = d.series[i];
            s+='<tr><th contenteditable="true">'+r.name+'</th>';
            for (j=0; j<r.data.length; j++) s+='<td contenteditable="true">'+r.data[j]+'</td>';
            s+='</tr>';
          }
          $(sel+' tbody').html(s);          
       }

       ajx('/pages/sales/LoadPortfolioSummaries', {id:row_id}, function(d){
            // console.log(d);            
            
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
              '<td><button class="btn btn-sm b-del btn-danger">Delete</button></td></tr>';
                  }                
                  $(selector+' .opt-list').html(s);                  
                  $(selector+' .opt-list .b-del').click(deleteOption);
            }           
            
            if (d.row.bar!=undefined && d.row.bar.title!=undefined) $(selector+' .modal #bar_title').val(d.row.bar.title);
            if (d.row.line!=undefined && d.row.line.title!=undefined) $(selector+' .modal #line_title').val(d.row.line.title);
            if (d.row.comparison_id!=undefined) $(selector+' #comparison').val(d.row.comparison_id);
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
      $(selector+' .modal #bar_title').val('');
      $(selector+' .modal #line_title').val('');
      $(selector+' .modal #description').val('');
      // $(selector+' #social_value_metric').val('');
      // $(selector+' #esg_score').val('');
      $(selector+' .bar-chart thead').html('<tr><th>Series</th></tr>');
      $(selector+' .bar-chart tbody').html('');
      $(selector+' .line-chart thead').html('<tr><th>Series</th></tr>');
      $(selector+' .line-chart tbody').html('');
      $(selector+' tbody.opt-list').html('');
     // $(selector+' tbody.metrics-list').html('');
      $(selector+' #comparison').val("");
    }
    
    function save()
    { var d = {};
      d.description = $(selector+' .modal #description').val();
      d.portfolio_id = id;
      d.comparison_id = $(selector+' #comparison').val();
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
      d.bar.title = $(selector+' .modal #bar_title').val();
      d.bar.columns = getColumns('.bar-chart');
      d.bar.series = getSeries('.bar-chart');
      
      d.line = {};
      d.line.title = $(selector+' .modal #line_title').val();
      d.line.columns = getColumns('.line-chart');
      d.line.series = getSeries('.line-chart');
      
      if (insert_id!=null) d.id = insert_id;

      ajx('/pages/sales/SavePortfolioSummaries', d, function(dd){
            if (!dd.error) setOk(dd.info); 
            if (insert_id!=null) $(selector+' .modal').modal('hide');
            if (dd.insert_id!=undefined) insert_id = dd.insert_id;
            if (!dd.error && onaftersave!=null) onaftersave(dd);
       });
            
     // console.log(d);
      
    }
    

    
    function afterSave(foo){ onaftersave=foo; }
    
    $(selector+' .b-add-category').click(function(){
          $(selector+' .opt-list').append('<tr><td><input type="checkbox" /></td>'+
          '<td contenteditable="true">Option</td>'+
          '<td><button class="btn btn-sm b-del btn-danger">Delete</button></td></tr>');          
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

function createSnapshotTab(pf_id)
{  ajx('/pages/sales/GetPortfolioName',{id:pf_id}, function(d){
        var num = $('ul.w-sumtabs li').length;
        var id = 'snap'+num;
        // /html.php/pages/sales/portfolio-snapshot/
        // /sales/portfolio-summaries/snapshot/
        var url = window.location.origin+'<?php echo mkURL("/html.php/pages/sales/portfolio-snapshot/") ?>'+pf_id;
        // creating of a dynamic tabs
        // <button type="button" class="btn btn-default pull-right b-copy-url"> <span class="glyphicon glyphicon-tags"></span> Copy URL to clipboard</button>
        $('ul.w-sumtabs').append('<li id="i'+id+'"><a target="_blank" data-toggle="tab" href="#'+id+'">Snapshot: '+d.row.portfolio+'<span id="pfsnap"></span></a></li>');        
        $('div.tab-content').append('<div class="tab-pane fade" id="'+id+'">\
        <div class="row"><div class="col-lg-10">\
        <a class="pull-right" target="_blank" href="'+url+'"><span class="glyphicon glyphicon-tags"></span> '+url+'</a>\
        </div></div>\
       <div class="row">\
        <div class="col-lg-10">\
                <div id="ch'+id+'" style="min-width: 450px; height: 350px; margin: 0 auto"></div>\
        </div>\
        <div class="col-lg-2">\
        </div>\
     </div>\
</div>');
        ajx('/pages/sales/GetSnapshots',{pf_id:pf_id}, function(dd){
            dd.title = d.row.portfolio;
            portfolioSnapshotChart('ch'+id, dd);
            $('#i'+id+' a').tab('show'); // activate created tab
            /*
            $('#'+id+' .b-copy-url').click(function(){                
               $('#'+id+' a')[0].select();
               document.execCommand('copy');
               // var copyEvent = new ClipboardEvent('copy', { dataType: 'text/plain', data:url } );
               // document.dispatchEvent(copyEvent);
                
            });
            */
        });  
   });
}

$(function(){
     
  // var dialog;
  var views = new htviewCached();
  
 
  var pager;  
  var soc_metric;
  var esg_score;

    
  var settings = new editSettings('#spset', function(d){
       
       // create with default values after loading
       if (d.row!=undefined && d.row.esg_metric_id!=undefined) 
          esg_score = new mdSelect('#esg_score', d.row.esg_metric_id);
       else  
          esg_score = new mdSelect('#esg_score'); 
     
       if (d.row!=undefined && d.row.metric_id!=undefined)  
          soc_metric = new mdSelect('#social_value_metric', d.row.metric_id); 
       else
          soc_metric = new mdSelect('#social_value_metric');
      
  }); 
  
  var ss_compar = new mdSelect('#ss_comparison');
  
  var snapsettings = new editSnapshotSettings('#psnap');
  
   var view = new createCustomModelView('<div class="btn-group pull-right">\
   <button class="btn btn-sm b-new">New summary</button>\
   <button data-id="{id}" class="btn btn-sm b-open-snapshot btn-primary">Get snapshot</button>\
   </div>', function(){      
      $('button.b-new').click(function(e){
            edit.addNew();      
      });
       $('button.b-open-snapshot').click(function(e){
            createSnapshotTab(e.target.getAttribute('data-id'));
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
    
   
   function updateSummaryView(row_id)
   {   ajx('/pages/sales/LoadPortfolioSummaries', {id:row_id}, function(d){
                var st = settings.getData();
                views.view('/pages/sales/pfsummary','#pfsummary', function(){
                    var i;
                    var url = window.location.origin+'<?php echo mkURL("/html.php/pages/sales/portfolio-summary/") ?>'+row_id;
                    $('#pfsummary .w-sum-url span.text').html(url);
                    $('#pfsummary a.w-sum-url').attr('href',url);
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
                    $('#pfsummary').attr('data-id', row_id);
                    if (d.row.portfolio!=undefined) $('#p-name').html(d.row.portfolio);
                    if (d.row.description!=undefined) 
                    { $('#p-description').html(d.row.description);
                      $('#pfname').html(d.row.description);      
                    }
                    ajx('/pages/sales/LoadSummaryDescriptions', {}, function(d){
                       var desc = d.row;
                        $('#pfsummary .sum-title').each(function(i, e){
                                if (desc[i]!=undefined)
                                {  $(e).html(desc[i].title);
                                   $(e).next().html(desc[i].descr);
                                }           
                           });
                    });
                    if (d.row.bar!=undefined) renderBarChart(d.row.bar);
                    if (d.row.line!=undefined) renderLineChart(d.row.line);
                    if (d.row.comparison_id!=undefined) themeExposuresChart(d.row.portfolio_id, d.row.comparison_id);
                    if (d.row.comparison_id!=undefined && st.metric_id!=undefined)                     
                        socialChart(d.row.portfolio_id, d.row.comparison_id, st.metric_id);
                    if (st.metrics!=undefined) metricsAnalysis(st.metrics, d.row.portfolio_id, d.row.comparison_id);
                    if (st.esg_metric_id!=undefined) esgAnalysis2(d.row.portfolio_id, st.esg_metric_id, d.row.comparison_id); 
                    
                    $('h3.sum-title:first').focus()
                    $('.w-save-desc').click(function(e){
                        var desc = [];
                           $('#pfsummary .sum-title').each(function(i, e){
                                desc[i] = {title:$(e).html(), descr:$(e).next().html()};
                           });
                           ajx('/pages/sales/SaveSummaryDescriptions', {data:desc}, function(d){
                               if (!d.error) setOk(d.info);
                          });
                    });
    
                });

                // if (dd.insert_id!=undefined) insert_id = dd.insert_id;
           });
   }
   
   model_sum = new modelListController('#tabedit .model-list', model_view);
   model_sum.load();
   model_sum.last_id = null;
   model_sum.prev_id = null;
   model_sum.click(function(e, row){
        model_sum.last_id = row.id;
        $('#pfname').html(row.description);        
   });
   
   $('.w-sumtabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (e.target.getAttribute('href')=='#tabschart')
        {  if (model_sum.prev_id!=model_sum.last_id)
           {  model_sum.prev_id = model_sum.last_id;
              updateSummaryView(model_sum.last_id);             
           }
        }
   })
   
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
            $('#schart a').tab('show');
            if (model_sum.last_id!=null) updateSummaryView(model_sum.last_id);            
           // $('#tbedit a[href="#tabedit"]').tab('show');
       });
       var compar = new mdSelect('#comparison');
    });
    
    // Process windows location    
    var apath = window.location.pathname.split('/sales/portfolio-summaries');
    if (apath.length>1)
    {   apath = apath[1].split('/');
        if ((apath.length>2) && apath[1].toLowerCase()=='snapshot')
        {  createSnapshotTab(apath[2])            
        }
    }
   
});

